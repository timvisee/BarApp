// /**
//  * First we will load all of this project's JavaScript dependencies which
//  * includes Vue and other libraries. It is a great starting point when
//  * building robust, powerful web applications using Vue and Laravel.
//  */
//
// require('./bootstrap');
//
// window.Vue = require('vue');
//
// /**
//  * Next, we will create a fresh Vue application instance and attach it to
//  * the page. Then, you may begin adding components to this application
//  * or customize the JavaScript scaffolding to fit your unique needs.
//  */
//
// Vue.component('example', require('./components/Example.vue'));
//
// const app = new Vue({
//     el: '#app'
// });

$(document).ready(function() {
    // Sidebar toggle
    $('.sidebar-toggle').click(function() {
        let sidebarClass = $(this).data('sidebar');
        $('.ui.sidebar.' + sidebarClass).sidebar('toggle');
        return false;
    });

    // Initialize components
    $('.ui.checkbox').checkbox();
    $('.ui.dropdown').dropdown();
    $('.ui.accordion').accordion();

    // Join label popup
    $('.joined-label-popup').popup({
        position: 'bottom left',
        transition: 'vertical flip',
    });

    // Join label popup
    $('.popup').popup({
        position: 'top center',
        transition: 'scale',
    });

    // Copy on clock for copy elements
    // TODO: translate
    $('.copy').click(function() {
        // Get the node, select the text
        var copyText = $(this);
        var origText = copyText.text();
        var altText = copyText.data('copy');

        copyText.data('tooltip', 'blah');

        // Modify text before copy if alt text
        if(altText)
            copyText.text(altText);

        // Select the text and copy
        selectText(copyText.get()[0]);
        document.execCommand("copy");

        // Reset original text, select it again for visuals
        if(altText) {
            copyText.text(origText);
            selectText(copyText.get()[0]);
        }

        // Show in popup that we've copied
        copyText.popup('change content', 'Copied!');
    });
    $('.copy').popup({
        content: 'Click to copy',
        position: 'right center',
    });
});

/**
 * Select all text in the given DOM node.
 */
function selectText(node) {
    // node = document.getElementById(node);

    if (document.body.createTextRange) {
        const range = document.body.createTextRange();
        range.moveToElementText(node);
        range.select();
    } else if (window.getSelection) {
        const selection = window.getSelection();
        const range = document.createRange();
        range.selectNodeContents(node);
        selection.removeAllRanges();
        selection.addRange(range);
    } else {
        console.warn("Could not select text in node: Unsupported browser.");
    }
}
