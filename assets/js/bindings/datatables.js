
var $ = require('jquery');
require('datatables');
require('datatables.bootstrap');

module.exports = function () {
    $('.datatables').DataTable({
        order: [[0, 'desc']]
    });
};