<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use App\Helpers\ValidationDefaults;
use App\Models\Product;

class ProductController extends Controller {

    /**
     * Products index page.
     * This shows the list of products in the current economy.
     *
     * @return Response
     */
    public function index(Request $request, $communityId, $economyId) {
        // Get the community, find the products
        $search = \Request::get('q');
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $trashed = is_checked($request->query('trashed'));

        // Fetch products
        if(!empty($search))
            $products = $trashed
                ? $economy->searchProducts($search)->onlyTrashed()->sortBy('name')
                : $economy->searchProducts($search)->sortBy('name');
        else
            $products = $trashed
                ? $economy->products()->onlyTrashed()->orderBy('name')->get()
                : $economy->products()->orderBy('name')->get();

        return view('community.economy.product.index')
            ->with('economy', $economy)
            ->with('products', $products)
            ->with('trashed', $trashed);
    }

    /**
     * Product creation page.
     *
     * @return Response
     */
    public function create(Request $request, $communityId, $economyId) {
        // Get the community, find the products
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $locales = langManager()->getLocales(true, true);

        // Check whether to clone a product
        $cloneProductId = $request->query('productId');
        $clone = strlen($cloneProductId) > 0;
        $cloneProduct = $clone ? $economy->products()->withTrashed()->findOrFail($cloneProductId) : null;

        return view('community.economy.product.create')
            ->with('economy', $economy)
            ->with('locales', $locales)
            ->with('clone', $clone)
            ->with('cloneProduct', $cloneProduct);
    }

    /**
     * Product create endpoint.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function doCreate(Request $request, $communityId, $economyId) {
        // Get the community, find the products
        $user = barauth()->getUser();
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $locales = collect(langManager()->getLocales(true, true));
        $clone = $request->input('submit') == 'clone';

        // Build validation rules, and validate
        $rules = [
            'name' => 'required|' . ValidationDefaults::NAME,
            'tags' => 'nullable|' . ValidationDefaults::PRODUCT_TAGS,
        ];
        $messages = [];
        foreach($economy->currencies as $currency) {
            $rules['price_' . $currency->id] = 'nullable|' . ValidationDefaults::PRICE;
            $messages['price_' . $currency->id . '.regex'] = __('misc.invalidPrice');
        }
        foreach($locales as $locale)
            $rules['name_' . $locale] = 'nullable|' . ValidationDefaults::NAME;
        $this->validate($request, $rules, $messages);

        // Create product and set prices in transaction
        $product = null;
        DB::transaction(function() use($request, $user, $economy, $locales, &$product) {
            // Create the product
            $product = $economy->products()->create([
                'economy_id' => $economy->id,
                'type' => Product::TYPE_NORMAL,
                'name' => $request->input('name'),
                'tags' => $request->input('tags'),
                'created_user_id' => $user->id,
            ]);

            // Create the localized product names
            $product->names()->createMany(
                $locales
                    ->filter(function($locale) use($request) {
                        return $request->input('name_' . $locale) != null;
                    })
                    ->map(function($locale) use($request, $product) {
                        return [
                            'product_id' => $product->id,
                            'locale' => $locale,
                            'name' => $request->input('name_' . $locale),
                        ];
                    })
                    ->toArray()
            );

            // Create the product prices
            $product->prices()->createMany(
                $economy
                    ->currencies
                    ->filter(function($currency) use($request) {
                        return $request->input('price_' . $currency->id) != null;
                    })
                    ->map(function($currency) use($request, $product) {
                        return [
                            'product_id' => $product->id,
                            'currency_id' => $currency->id,
                            'price' => str_replace(',', '.', $request->input('price_' . $currency->id)),
                        ];
                    })
                    ->toArray()
            );
        });

        // Build the response, redirect
        $response = redirect();

        // Got to product index, or create page if cloning
        if(!$clone)
            $response = $response
                ->route('community.economy.product.index', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                ]);
        else
            $response = $response
                ->route('community.economy.product.create', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                    'productId' => $product->id,
                ]);

        // Attach success message and return
        return $response->with('success', __('pages.products.created'));
    }

    /**
     * Show a product.
     *
     * @return Response
     */
    public function show($communityId, $economyId, $productId) {
        // Get the community, find the product
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->withTrashed()->findOrFail($productId);

        return view('community.economy.product.show')
            ->with('economy', $economy)
            ->with('product', $product);
    }

    /**
     * Edit a product.
     *
     * @return Response
     */
    public function edit($communityId, $economyId, $productId) {
        // TODO: with trashed?

        // Get the community, find the product
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->findOrFail($productId);
        $locales = langManager()->getLocales(true, true);

        return view('community.economy.product.edit')
            ->with('economy', $economy)
            ->with('product', $product)
            ->with('locales', $locales);
    }

    /**
     * Product update endpoint.
     *
     * @param Request $request Request.
     *
     * @return Response
     */
    public function doEdit(Request $request, $communityId, $economyId, $productId) {
        // TODO: with trashed?

        // Get the community, find the product
        $user = barauth()->getUser();
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->findOrFail($productId);
        $locales = collect(langManager()->getLocales(true, true));

        // Build validation rules, and validate
        $rules = [
            'name' => 'required|' . ValidationDefaults::NAME,
            'tags' => 'nullable|' . ValidationDefaults::PRODUCT_TAGS,
        ];
        $messages = [];
        foreach($economy->currencies as $currency) {
            $rules['price_' . $currency->id] = 'nullable|' . ValidationDefaults::PRICE;
            $messages['price_' . $currency->id . '.regex'] = __('misc.invalidPrice');
        }
        foreach($locales as $locale)
            $rules['name_' . $locale] = 'nullable|' . ValidationDefaults::NAME;
        $this->validate($request, $rules, $messages);

        // Change product properties and sync prices in transaction
        DB::transaction(function() use($request, $user, $product, $economy, $locales) {
            // Change properties
            $product->name = $request->input('name');
            $product->tags = $request->input('tags');
            $product->updated_user_id = $user->id;
            $product->save();

            // Sync localized product names
            $product->names()->sync(
                $locales
                    ->filter(function($locale) use($request) {
                        return $request->input('name_' . $locale) != null;
                    })
                    ->map(function($locale) use($request, $product) {
                        return [
                            'id' => $product
                                ->names
                                ->filter(function($n) use($locale) { return $n->locale == $locale; })
                                ->map(function($n) { return $n->id; })
                                ->first(),
                            'product_id' => $product->id,
                            'locale' => $locale,
                            'name' => $request->input('name_' . $locale),
                        ];
                    })
                    ->toArray()
            );

            // Sync product prices
            $product->prices()->sync(
                $economy
                    ->currencies
                    ->filter(function($currency) use($request) {
                        return $request->input('price_' . $currency->id) != null;
                    })
                    ->map(function($currency) use($request, $product) {
                        return [
                            'id' => $product
                                ->prices
                                ->filter(function($p) use($currency) { return $p->currency_id == $currency->id; })
                                ->map(function($p) { return $p->id; })
                                ->first(),
                            'product_id' => $product->id,
                            'currency_id' => $currency->id,
                            'price' => normalize_price($request->input('price_' . $currency->id)),
                        ];
                    })
                    ->toArray()
            );
        });

        // Redirect the user to the account overview page
        return redirect()
            ->route('community.economy.product.show', [
                'communityId' => $community->human_id,
                'economyId' => $economy->id,
                'productId' => $product->id,
            ])
            ->with('success', __('pages.products.changed'));
    }

    /**
     * Page for confirming the deletion of the product.
     *
     * @return Response
     */
    public function delete($communityId, $economyId, $productId) {
        // Get the community, find the product
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->withTrashed()->findOrFail($productId);

        // TODO: ensure there are no other constraints that prevent deleting the
        // product

        return view('community.economy.product.delete')
            ->with('economy', $economy)
            ->with('product', $product);
    }

    /**
     * Delete a product.
     *
     * @return Response
     */
    public function doDelete(Request $request, $communityId, $economyId, $productId) {
        // Get the community, find the product
        $user = barauth()->getUser();
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->withTrashed()->findOrFail($productId);
        $permanent = is_checked($request->input('permanent'));

        // TODO: ensure there are no other constraints that prevent deleting the
        // product

        // Set last updating user
        $product->updated_user_id = $user->id;
        $product->save();

        // Delete, or soft delete
        if(!$permanent)
            $product->delete();
        else
            $product->forceDelete();

        // Redirect to the product index
        return redirect()
            ->route('community.economy.product.index', [
                'communityId' => $community->human_id,
                'economyId' => $economy->id
            ])
            ->with('success', __('pages.products.' . ($permanent ? 'permanentlyDeleted' : 'deleted')));
    }

    /**
     * Page for confirming restoring a product.
     *
     * @return Response
     */
    public function restore($communityId, $economyId, $productId) {
        // Get the community, find the product
        $user = barauth()->getUser();
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->withTrashed()->findOrFail($productId);

        // If already restored, redirect to the product
        if(!$product->trashed())
            return redirect()
                ->route('community.economy.product.show', [
                    'communityId' => $community->human_id,
                    'economyId' => $economy->id,
                    'productId' => $product->id,
                ])
                ->with('success', __('pages.products.restored'));

        // Set last updating user
        $product->updated_user_id = $user->id;
        $product->save();

        return view('community.economy.product.restore')
            ->with('economy', $economy)
            ->with('product', $product);
    }

    /**
     * Restore a product.
     *
     * @return Response
     */
    public function doRestore($communityId, $economyId, $productId) {
        // TODO: delete trashed, and allow trashing?

        // Get the community, find the product
        $user = barauth()->getUser();
        $community = \Request::get('community');
        $economy = $community->economies()->findOrFail($economyId);
        $product = $economy->products()->withTrashed()->findOrFail($productId);

        // Restore the product
        $product->restore();
        $product->updated_user_id = $user->id;
        $product->save();

        // Redirect to the product index
        return redirect()
            ->route('community.economy.product.show', [
                'communityId' => $community->human_id,
                'economyId' => $economy->id,
                'productId' => $product->id,
            ])
            ->with('success', __('pages.products.restored'));
    }

    /**
     * The permission required for viewing.
     * @return PermsConfig The permission configuration.
     */
    public static function permsView() {
        return EconomyController::permsView();
    }

    /**
     * The permission required for managing such as editing and deleting.
     * @return PermsConfig The permission configuration.
     */
    public static function permsManage() {
        return EconomyController::permsManage();
    }
}
