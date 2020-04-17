import ListItem from './item.js';
import ProductItem from './items/product.js';
import ProductTableRow from './items/productRow.js';
import CategoryTableRow from './items/categoryRow.js';
import CartProductItem from './items/cartProduct.js';
import FavoriteProductItem from './items/favoriteProduct.js';
import PurchaseItem from './items/purchase.js';
import Loader from './loader.js';
import Modal from './modal.js';
import Toast from './toast.js';

const cleanUpCallbacks = [
    {
        id: 'refresh_table',
        method: (entity, listItem, container, onRefresh = null) => {
            request ('filter', { entity: entity }, (response) => {
                if (response.success)
                {
                    // attempting to refresh view
                    ListItem.layout(container, listItem, response.success)
                    // extra operations
                    if (onRefresh != null) onRefresh();
                }
            });
        }
    },
    {
        id: 'refresh_table_category',
        method: () => { cleanUpCallbacks[0].method ('category', new CategoryTableRow(), '#list-categories') }
    },
    {
        id: 'refresh_table_product',
        method: () => { cleanUpCallbacks[0].method ('product', new ProductTableRow(), '#list-products') }
    },
    {
        id: 'refresh_cart_stats',
        method: () => {
            // Update select counter
            $('.txt-select-count').text ($('.selected').length);
            // Count the total price of selected items
            countTotal ();
        }
    }
];

const items = [
    {
        name: 'product_item',
        instance: new ProductItem()
    },
    {
        name: 'product_item_row',
        instance: new ProductTableRow()
    },
    {
        name: 'product_cart_item',
        instance: new CartProductItem()
    },
    {
        name: 'product_favorite_item',
        instance: new FavoriteProductItem()
    }
];

let isSignedin = false;

$(document).ready (function () {

    // Retrieve components
    let modal = new Modal('#modal-alert');
    let toast = new Toast('.toast');

    // When the page is ready, check if this client is signed in or not
    request ('isSignedIn', {}, (r) => { isSignedin = r; });

    // Selecting functionality
    $(document).on ('click', '.selectable', function () {
        $(this).toggleClass ('selected');
        $(this).find ('.select-indicator').toggleClass ('d-none');
        getCallback ('refresh_cart_stats').method ();
    });

    $('.btn-select-all').click (() => {
        $('.showcase-item > .row').toggleClass ('selected');
        $('.showcase-item > .row .select-indicator').toggleClass ('d-none');
        getCallback ('refresh_cart_stats').method ();
    });

    // Slider functionality
    $('.slideshow-handler').click (function () {
        // Retrieve the index
        let index = $(this).index ();
        // Hide all elements
        $('.slideshow-content .row').addClass ('d-none');
        // Show requested element
        $('.slideshow-content .row').eq (index).removeClass ('d-none');
        // Deactivate all handlers
        $('.slideshow-handler').removeClass ('active');
        // Activate clicked handler
        $(this).addClass ('active');
    });

    // Entity Table tabs
    $("#table-tab-controls button").click (function (){
        // Strip all buttons from active state
        $('#table-tab-controls button').removeClass ('active');
        // Activate the clicked one
        $(this).addClass ('active');
        // Hide all entity tables
        $('#table-container table').addClass ('d-none');
        // Show the corresponding table
        $('#table-container table').eq ($(this).index ()).removeClass ('d-none');
        // Hide all headers
        $('#table-headers > div').addClass ('d-none');
        // Show the matched header
        $('#table-headers > div').eq ($(this).index ()).removeClass ('d-none');
    });

    // Slight tweaks
    $('.btn-insert').click (function (){
        // Corresponding modal
        let form = $($(this).attr ('data-target')); 
        // Empty inputs
        form[0].reset ();
        // Change some texts
        form.find ('.modal-title').text ($(this).text ());
        form.find ('.btn-submit').text ('Add');
    });

    // Showcase item effects
    $(document).on ('mouseenter', '.showcase-item', function () {
        // Conditions to respect
        if (!isSignedin) return;
        if ($(this).find('.btn-star, .btn-add-to-cart').length == 0) return;
        // Show control button
        $(this).find ('.icon.clickable').removeClass ('d-none');
        // Verify if this product is already in the cart or marked as favorite
        for ( let o of [
            { entity: 'cart', target: '.btn-add-to-cart' },
            { entity: 'favorite', target: '.btn-star' }
        ])
        {
            if ($(this).find(o.target).length == 0) continue;
            // Send a request to check
            request ('filter', { entity: o.entity, product_id: $(this).attr('record-key') }, (r) => {
                if (r.success.length > 0)
                    $(this).find (o.target).addClass ('added');
                else
                    $(this).find (o.target).removeClass ('added');
            });
        }
    });
    $(document).on ('mouseleave', '.showcase-item', function () {
        $(this).find ('.icon.clickable').addClass ('d-none');
    });

    // Filter by category
    $('.filter-item').click (function () {
        // Displayed item text
        let itemText = $(this).attr('data-display');
        // Construct request data
        let data = { entity: $(this).attr ('filter-target') };
        data[$(this).attr ('filter-prop')] = $(this).attr ('data-key');
        // Change style
        $('.filter-item').removeClass('active');
        $(this).addClass('active');
        // Send request
        request ('filter', data, (response) => {
            if (response.success)
            {
                // Change the counter text
                $('#text-counter').html (`${itemText} - <span class='text-main'>${response.success.length}</span> results`);
                // Refill item list
                ListItem.layout ($(this).attr('item-container'), getInstance($(this).attr('item-name')).instance, response.success);
                // Invoke callback methods if there are any
                let callbacks = getCallbacks($(this));
                if (callbacks != null) invoke(callbacks);
            }
        });
    });
    $('.cmb-category').on ('change', function () {
        // Create a data array
        let data = {};
        // Fill the data array
        data['entity'] = $(this).attr('filter-target');
        data[$(this).attr('filter-prop')] = $(this).val ();
        // Send a request
        // If selected option is -1, then select all
        request ('filter', $(this).val() == -1 ? { entity: data.entity } : data, (response) => {
            ListItem.layout ('#list-products', new ProductTableRow(), response.success);
        });
    });

    // Filter by product's name
    $('.btn-search').click (function () {
        // Retrieve criteria
        let name = $(`*[name='in_name']`).val ();
        // Construct a data object
        let data = {
            entity: 'product',
            name: [name, 4 /** The id of the 'contains' operator */]
        };
        // Send a request
        request ('filter', name.length == 0 ? { entity:'product' } : data, (response) => {
            ListItem.layout ('#list-products', new ProductTableRow(), response.success);
        });
    });

    // Edit
    $(document).on ('click', '.btn-edit', function () {
        let settings = {
            formId: $(this).attr('data-target'),
            entity: $(this).attr('target-entity'),
            recordKey: $(this).attr('record-key')
        };
        // Retrieve the targeted record
        request ('filter', { entity: settings.entity, id: settings.recordKey }, (r) => {
            if (r.hasOwnProperty ('success'))
            {
                // Retrieve form
                let form = $(settings.formId);
                // Get record
                let record = r.success[0];
                // Fill form
                for (let property in record)
                {
                    if (property == 'photo') continue;
                    if (record[property] == null) continue;

                    form.find (`*[name='${property}']`).val (record[property].hasOwnProperty('id') ? record[property].id : record[property]);
                }
                // Associate record key
                form.find('.btn-submit').attr ('record-key', settings.recordKey);
                // Change the modal's appeareance
                form.find('.modal-title').text (`Edit Selected Category (id=${settings.recordKey})`);
                form.find('.btn-submit').text ('Update');
            }
        });
    });

    // Insert & Update
    $('.btn-submit').click (function () {
        // Gather data
        let formId = $(this).attr('data-form-id');
        let recordId = $(this).attr('record-key');
        // Construct a form data
        let data = new FormData ($(formId) [0]);
        data.append('entity', $(this).attr('target-entity'));
        if (typeof recordId !== typeof undefined && recordId !== false) data.append('id', recordId);
        // Send a request
        request ((!data.has('id') ? 'insert' : 'update'), data, (response) => {
            // Refresh
            if (response.success) invoke (getCallbacks ($(this)));
            else toast.show('Error', response.error);
        }, null, { contentType: false, processData: false });
    });

    // Delete
    $(document).on ('click', '.btn-delete', function () {
        // Send request
        request ('delete', {
            entity: $(this).attr('target-entity'),
            id: $(this).attr ('record-key')
        }, (response) => {
            // Refresh
            if (response.success) invoke (getCallbacks ($(this)));
        });
    });

    // Adding to cart/favorites
    $(document).on ('click', '.btn-add-to-cart:not(.added), .btn-star:not(.added)', function () {
        // Retrieve essential data
        let recordKey = $(this).closest ('.showcase-item').attr ('record-key');
        let entity = $(this).attr('target-entity');
        // Send a request
        request ('addToUser', { entity: entity, product: recordKey }, (r) => {
            if (r.success) 
            {
                $(this).addClass ('added');
                toast.show('Success', `Added to ${entity} list!`);
            } 
        });
    });

    // Removing from cart/favorites
    $(document).on ('click', '.added', function () {
        request ('deleteToUser', { entity: $(this).attr('target-entity'), product_id: $(this).closest ('.showcase-item').attr ('record-key') }, (r) => {
            if (r.success) $(this).removeClass ('added');
        });
    });

    // Cart item incrementing/decrementing unit quantity
    $(document).on ('click', '.arrow-increment, .arrow-decrement', function () {
        // Get the item that holds this arrow
        let item = $(this).closest ('.showcase-item');
        // Search for 
        let qty = parseInt (item.attr ('data-quantity'));
        let maxQty = parseInt (item.attr ('data-max-quantity'));
        let price = parseFloat (item.attr ('data-price'));
        // Increment/decrement
        qty += $(this).hasClass('arrow-increment') ? 1 : -1;
        qty = qty < 0 ? 0 : qty;
        qty = qty > maxQty ? maxQty : qty;
        // Update
        item.attr ('data-quantity', qty);
        item.find ('.txt-units').text (`${qty} Units`);
        item.find ('.txt-units').removeClass(qty == maxQty ? 'bg-main' : 'bg-danger').addClass(qty == maxQty ? 'bg-danger' : 'bg-main');
        item.find ('.txt-total').text (`${qty * price} $`);
        getCallback('refresh_cart_stats').method ();
    });

    // Remove from cart/favorite
    $('.btn-remove').click (function () {
        // Delete each selected item
        for ( let i of $('.showcase-item:has(.selected)') )
            request ('deleteToUser', { entity: $(this).attr('target-entity'), product_id: $(i).attr('record-key') }, (r) => { 
                if (r.success)
                {
                    // Remove item
                    $(i).fadeOut (500, function () {
                        $(this).remove ();
                        // Refresh stats
                        getCallback('refresh_cart_stats').method (); 
                    });
                }
            });
    });

    // Purchase operation
    $('.btn-purchase').click (function () {
        // retrieve selected product items
        let items = $('.showcase-item:has(.selected)'), productIds = [], quantities = [];
        // check if there are selected items
        if (items.length == 0)
        {
            modal.show('Information', 'There are no selected items to purchase!');

            return;
        }
        // fill data
        for (let i of items)
        {
            if ($(i).attr('data-quantity') == '0')
                continue;

            productIds.push ( $(i).attr('record-key') );
            quantities.push ( $(i).attr('data-quantity') );
        }
        // checkk
        if (productIds.length == 0)
            modal.show('Error', 'Make sure you ordered something!');
        else
        // purchase request
        request ('purchase', { ids: productIds, quantities: quantities }, (r) => {
            if (r.success)
            {
                // refresh list
                $('.showcase-navigation li.active').trigger ('click');
                // if there's no active category, just refresh all
                if ($('.showcase-navigation li.active').length == 0)
                    getCallback('refresh_table').method('cart', new CartProductItem(), '#list-products', getCallback('refresh_cart_stats').method);
            }
            else modal.show('Error', r.error);
        });
    });

    // Filter by date
    $('.btn-filter-date').click (function () {
        request ('filter', { entity: 'purchase', date: [$('*[name="start"]').val(), 7] }, (r1) => {
            if (r1.success)
            {
                // Get the loader
                let loader = new Loader(2, 1);
                loader.takeStep ();
                // Complete filter operation
                request ('filter', { entity: 'purchase', date: [$('*[name="end"]').val(), 8] }, (r2) => {
                    if (r2.success)
                    {
                        // Get the items who met both conditions 
                        let intersection = r1.success.filter (i => r2.success.find (j => j.id == i.id) != null);
                        // onComplete 
                        let complete = () => {
                            // Calculate purchase's total price
                            let purchaseTotal = (purchase) => {
                                let total = 0;

                                for (let detail of purchase.purchaseDetails)
                                    total += detail.quantity * detail.product.price;

                                purchase['totalPrice'] = total;

                                return total;
                            };
                            // Calculate the sum of all purchase's total prices
                            let total = 0; intersection.forEach ((purchase) => { total += purchaseTotal(purchase); });
                            // Refresh item container
                            ListItem.layout ('#list-purchases', new PurchaseItem(), intersection);
                            // Update out labels
                            $('.out-purchase-count').text (intersection.length);
                            $('.out-purchase-total').text (`${total} $`);
                        };
                        // Reset loader anyways
                        loader.reset ();
                        // Check if there are positive results
                        if (intersection.length == 0) 
                        {
                            complete ();

                            return;
                        }
                        // Update loader settings
                        loader.capacity = intersection.length;
                        // Recursive progressive function
                        let func = (index, onFinish) => {
                            if (index == intersection.length)
                            {
                                onFinish ();
                                return;
                            }
                            // retrieve purchase
                            let purchase = intersection[index];
                            // Get all purchase details
                            request ('filter', { entity: 'purchaseDetail', purchase_id: purchase.id }, (r) => { 
                                // Update purchase's details array
                                intersection[index].purchaseDetails = r.success; 
                                // Increment loader
                                loader.takeStep ();
                                // Increment index, then recall func
                                func (++index, onFinish);
                            });
                        };
                        // Start the function
                        func (0, complete);
                    }
                });
            }
        });
    });

    // Sign in
    $('.btn-signin').click (function () {
        // Retrieve loader
        let loader = new Loader(2, 1);
        loader.takeStep();
        // Gather data
        let formData = $('.modal-sign-in').serialize ();
        // Request signing in
        request ('signIn', formData, (r) => {
            // Refresh page
            if (r.success)
            {
                loader.takeStep();
                setTimeout (() => {window.location.reload ()}, 1250);
            } 
            // Show error message
            else 
            {
                loader.reset();
                toast.show('Error', r.error);
            }
        });
    });

    // Sign up
    $('.btn-signup').click (function () {
        // Retrieve form data
        let formData = new FormData ($('.modal-sign-up')[0]);
        formData.append('entity', 'user');
        formData.append('type', 'customer');
        // Request
        request ('signUp', formData, (r) => {
            // Adjust settings
            let options = r.success ? { title: 'Information', message: r.success } : { title: 'Alert', message: r.error }; 
            // Show modal
            toast.show (options.title, options.message);
        }, null, { processData: false, contentType: false });
    });

});

/** AJAX Request */
function request (url, data, onSuccess, onFail=null, other=null)
{
    let options = {
        type: 'POST',
        url: url,
        data: data,
        dataType: 'JSON',
        success: onSuccess,
        fail: onFail
    };

    if (other != null)
        options = { ...options, ...other }

    $.ajax (options);
}

/** Retrieve the clean up callbacks from an html element */
function getCallbacks (element)
{
    if (!element[0].hasAttribute ('cleanup'))
        return;

    let idArray = element.attr ('cleanup').split (',');

    return cleanUpCallbacks.filter (link => { return idArray.includes(link.id); });
}

/** Retrieve a callback using its id  */
function getCallback (id)
{
    return cleanUpCallbacks.find (i => i.id == id);
}

/** Invoke all callbacks */
function invoke (callbacks)
{
    for ( let callback of callbacks ) callback.method ();
}

/** Count the total price */
function countTotal ()
{
    let total = 0;

    for (let i of $('.showcase-item:has(.selected)'))
        total += parseInt ($(i).attr ('data-quantity')) * parseInt ($(i).attr ('data-price'));

    $('.txt-purchase-total').text (total);
}

/** Retrieve the instance */
function getInstance (name)
{
    return items.find(i => i.name == name);
}