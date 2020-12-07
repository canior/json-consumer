/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
window.$ = window.jQuery = require('jquery');

// jQuery 3 this not working
// var $ = require('admin-lte/bower_components/jquery/dist/jquery.min.js');
// jQuery UI 1.11.4
require('admin-lte/bower_components/jquery-ui/jquery-ui.min.js');
// Resolve conflict in jQuery UI tooltip with Bootstrap tooltip
// <script>
//     $.widget.bridge('uibutton', $.ui.button);
// </script>
// Bootstrap 3.3.7
require('admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js');
// Morris.js charts
window.Raphael = require('admin-lte/bower_components/raphael/raphael.min.js');
require('admin-lte/bower_components/morris.js/morris.min.js');
// Sparkline
require('admin-lte/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js');
// jvectormap
require('admin-lte/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js');
require('admin-lte/plugins/jvectormap/jquery-jvectormap-world-mill-en.js');
// jQuery Knob Chart
require('admin-lte/bower_components/jquery-knob/dist/jquery.knob.min.js');
// daterangepicker
// fix moment is not defined
window.moment = require('admin-lte/bower_components/moment/min/moment-with-locales.min');
moment.locale('zh-cn');
require('admin-lte/bower_components/bootstrap-daterangepicker/daterangepicker.js');
// datepicker
require('admin-lte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js');
// Bootstrap WYSIHTML5
// require('admin-lte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js');
// Slimscroll
require('admin-lte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js');
// FastClick
require('admin-lte/bower_components/fastclick/lib/fastclick.js');
// iCheck
require('admin-lte/plugins/iCheck/icheck.min.js');
// AdminLTE App
require('admin-lte/dist/js/adminlte.min.js');
// AdminLTE for demo purposes of control-sidebar
// require('../backend/js/control-sidebar.js')
// plugins
// Dropzone
window.Dropzone = require('dropzone/dist/min/dropzone.min');
window.Dropzone.autoDiscover = false;
// Select2
// require('select2/dist/js/select2.min');
// Chosen
require('chosen-js/chosen.jquery.min');

// initial
require('../backend/js/init');

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

