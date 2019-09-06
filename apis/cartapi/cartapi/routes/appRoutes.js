'use strict';
module.exports = function(app) {
    var cartfunc = require('../controllers/appController.js');

    // todoList Routes
    app.route('/cart')
        .post(cartfunc.add_to_cart)
        .get(cartfunc.list_cart_items)
        .delete(cartfunc.empty_cart)
        .put(cartfunc.clean_cart);
    
    app.route('/cart/:productId')
        .get(cartfunc.get_cart_item)
        .put(cartfunc.update_cart_item)
        .delete(cartfunc.delete_cart_item);
    
    app.route('/_health_check')
        .get(cartfunc.is_alive);
};
