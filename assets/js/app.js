import './styles/app.scss';
import greet from './greet';

import * as $ from 'jquery';
global.$ = global.jQuery = $;

 $(document).ready(function() {
     $('body').prepend('<h1>'+greet('jill')+'</h1>');
 });


require('bootstrap');


$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
