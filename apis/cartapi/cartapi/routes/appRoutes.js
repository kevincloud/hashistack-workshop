'use strict';
module.exports = function(app) {
    var cartfunc = require('../controllers/appController.js');

    // todoList Routes
    app.route('/cart')
        .post(cartfunc.add_to_cart)
        .get(cartfunc.list_cart_items);
    
    app.route('/cart/:productId')
        .put(cartfunc.update_cart_item)
        .delete(cartfunc.delete_cart_item);
    
    
};
