
jQuery(document).ready(function($) {
// Контейнер с контентом
let box = $('#ajax_filter_search_results');

ajaxPage();

var currLink = 1;

box.on( 'click', '.nav-links a', function (e) {
    e.preventDefault();

    let linkPage = $(this).attr('href');
    let titlePage = $(this).text();

    history.pushState({page_title: titlePage},'', linkPage);

    ajaxPage(linkPage);

});


// Ajax функция
function ajaxPage(linkPage) {

    $.ajax({
        url: ajax_pagination.ajaxurl,
        data : {
            'action': 'ajaxpagination',
            'link': linkPage,
            'posts': ajax_pagination.posts,
        },
        type: 'POST',
        beforeSend : function ( response ) {
            box.animate({opacity: 0.7}, 300);
        },
        success : function( posts ){
            if( posts ) {
                box.html( posts ); // insert new posts
            }
            box.animate({opacity: 1}, 300);
        }

    });

}

});
