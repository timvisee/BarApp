<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Helpers\ValidationDefaults;
use App\Perms\Builder\Builder;
use App\Perms\Builder\Config as PermsConfig;
use App\Perms\CommunityRoles;

class CommunityMemberController extends Controller {

    const PAGINATE_ITEMS = 50;

    /**
     * Community member index page.
     *
     * @return Response
     */
    public function index(Request $request) {
        // Get the community, list it's members
        $community = \Request::get('community');
        $search = $request->query('q');
        $members = $community
            ->members()
            ->search($search)
            ->orderBy('role', 'DESC')
            ->orderBy('visited_at', 'DESC')
            ->paginate(self::PAGINATE_ITEMS);

        return view('community.member.index')
            ->with('members', $members)
            ->with('search', $search);
    }

    /**
     * Show a member of a community with the given user ID.
     *
     * @return Response
     */
    public function show($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->members()->findOrFail($memberId);

        return view('community.member.show')
            ->with('member', $member);
    }

    /**
     * The edit page for a community member.
     *
     * @return Response
     */
    public function edit($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->members()->findOrFail($memberId);

        // Current role must be higher than user role
        $config = Builder::build()->raw(CommunityRoles::SCOPE, $member->role)->inherit();
        if(!perms($config))
            return redirect()
                ->route('community.member.show', ['communityId' => $communityId, 'memberId' => $memberId])
                ->with('error', __('pages.communityMembers.cannotEditMorePermissive'));

        // Show the edit view
        return view('community.member.edit')
            ->with('member', $member);
    }

    /**
     * Edit a community member.
     *
     * @return Response
     */
    public function doEdit(Request $request, $communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->members()->findOrFail($memberId);
        $curRole = $member->role;
        $newRole = $request->input('role');

        // Current role must be higher than user role
        $config = Builder::build()->raw(CommunityRoles::SCOPE, $curRole)->inherit();
        if(!perms($config))
            return redirect()
                ->route('community.member.show', ['communityId' => $communityId, 'memberId' => $memberId])
                ->with('error', __('pages.communityMembers.cannotEditMorePermissive'));

        // Build validation rules, validate
        $rules = [
            'role' => 'required|' . ValidationDefaults::communityRoles(),
        ];
        if($newRole != $curRole)
            $rules['confirm_role_change'] = 'accepted';
        $this->validate($request, $rules);

        // New role cannot be higher than what the user has (with inherited)
        $config = Builder::build()->raw(CommunityRoles::SCOPE, $newRole)->inherit();
        if(!perms($config))
            return redirect()
                ->route('community.member.show', ['communityId' => $communityId, 'memberId' => $memberId])
                ->with('error', __('pages.communityMembers.cannotSetMorePermissive'));

        // If manager or higher changed to lower role, and he was the last with
        // that role or higher, do not allow the change
        if($newRole < $curRole && $curRole > CommunityRoles::USER) {
            $hasOtherRanked = $community
                ->memberUsers(['id', 'role'], true)
                ->where('community_member.id', '<>', $memberId)
                ->where('community_member.role', '>=', $curRole)
                ->limit(1)
                ->exists();
            if(!$hasOtherRanked)
                return redirect()
                    ->route('community.member.show', ['communityId' => $communityId, 'memberId' => $memberId])
                    ->with('error', __('pages.communityMembers.cannotDemoteLastManager'));
        }

        // Set the role ID, save the member
        $member->role = $newRole;
        $member->save();

        // Redirect to the show view after editing
        return redirect()
            ->route('community.member.index', ['communityId' => $communityId])
            ->with('success', __('pages.communityMembers.memberUpdated'));
    }

    /**
     * The page to delete a community member.
     *
     * @return Response
     */
    public function delete($communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->members()->findOrFail($memberId);

        // Do some delete checks, return on early response
        if(($return = $this->checkDelete($community, $member)) != null)
            return $return;

        return view('community.member.delete')
            ->with('member', $member);
    }

    /**
     * Make a member leave the community.
     *
     * @return Response
     */
    public function doDelete(Request $request, $communityId, $memberId) {
        // Get the community, find the member
        $community = \Request::get('community');
        $member = $community->members()->findOrFail($memberId);

        // Validate confirmation when deleting authenticated member
        if($member->user_id == barauth()->getSessionUser()->id)
            $this->validate($request, ['confirm_self_delete' => 'accepted']);

        // Do some delete checks, return on early response
        if(($return = $this->checkDelete($community, $member)) != null)
            return $return;

        // Delete the member
        $community->leave($member->user);

        // Redirect to the index page after deleting
        return redirect()
            ->route('community.member.index', ['communityId' => $communityId])
            ->with('success', __('pages.communityMembers.memberRemoved'));
    }

    /**
     * Do some checks before deleting a member.
     * Extracted into a separate method to prevent duplicate code.
     *
     * @param Community $community The community.
     * @param CommunityMember $member The community member.
     *
     * @return null|Response Null to do nothing, or an early response.
     */
    private function checkDelete($community, $member) {
        // Get the current role
        $curRole = $member->role;

        // Cannot delete last member with this (or higher) management role
        // TODO: allow demote if manager/admin inherited from community
        if($curRole > CommunityRoles::USER) {
            $hasOtherRanked = $community
                ->members()
                ->where('community_member.id', '<>', $member->id)
                ->where('community_member.role', '>=', $curRole)
                ->limit(1)
                ->exists();
            if(!$hasOtherRanked)
                return redirect()
                    ->route('community.member.show', ['communityId' => $community->id, 'memberId' => $member->id])
                    ->with('error', __('pages.communityMembers.cannotDeleteLastManager'));
        }
    }

    /**
     * The permission required for viewing.
     * @return PermsConfig The permission configuration.
     */
    public static function permsView() {
        return CommunityController::permsManage();
    }

    /**
     * The permission required for managing such as editing and deleting.
     * @return PermsConfig The permission configuration.
     */
    public static function permsManage() {
        // TODO: community managers should be able to change some settings?
        return CommunityController::permsAdminister();
    }
}
