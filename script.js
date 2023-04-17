jQuery(document).ready(function($) {
$ = jQuery;   
let mainBox = $('#ajax_filter_search_results');
var page = 1;

$("#balcony").on('change', function() {
    if ($(this).is(':checked')) {
      $(this).attr('value', 'true');
    } else {
      $(this).attr('value', '');
    }
}); 

$("#bedroom").on('change', function() {
    if ($(this).is(':checked')) {
      $(this).attr('value', 'true');
    } else {
      $(this).attr('value', '');
    }
}); 

$('#clear').click(function(e){
    e.preventDefault();
    $(this).closest('form').find("input[type=text]").val("");
    mainBox.empty();
    var html = "<p class='error'>Почніть свій пошук прямо зараз.</p>";
    mainBox.append(html);
});

$('#search').click(function(e){
    e.preventDefault(); 

    if($("#name_house").val().length !== 0) {
        var name_house = $("#name_house").val();
    }  
    if($("#floors").val().length !== 0) {
        var floors = $("#floors option").filter(":selected").val();
    }
    if($("#coordinate").val().length !== 0) {
        var coordinate = $("#coordinate").val();
    }
    if($("#type").val().length !== 0) {
        var type_house = $("#type:checked").val();
    }
    if($("#ecology").val().length !== 0) {
        var ecology = $("#ecology option").filter(":selected").val();
    }
    if($("#balcony").val().length !== 0) {
        var balcony = $("#balcony").val();
    }
    if($("#square").val().length !== 0) {
        var square = $("#square option").filter(":selected").val();
    }
    if($("#rooms").val().length !== 0) {
        var rooms = $("#rooms option").filter(":selected").val();
    }
    if($("#bedroom").val().length !== 0) {
        var bedroom = $("#bedroom").val();
    }

    var linkPage = $('.nav-links a').attr('href');
    mainBox.addClass('filtered');

    var data = {
        action : "my_ajax_filter_search",
        search : search,
        name_house : name_house,
        floors : floors,
        coordinate : coordinate,
        type_house : type_house,
        ecology : ecology,
        square : square,
        rooms : rooms,
        balcony : balcony,
        bedroom : bedroom,
        page : page,
        nonce: search.nonce,
        'link': linkPage,
    }
        $.ajax({
            url : search.ajaxurl,
            type: 'POST',
            data : data,
            beforeSend : function ( response ) {
                mainBox.animate({opacity: 0.7}, 300);
            },
            success : function(response) {
                mainBox.empty();
                if(response) {
                    for ( var i = 0; i < response.length; i++ ) {
                        for ( var j = 0; j < response[i].compared.length; j++ ) {
                                var htm  = "<li id='real-estate-" + response[i].compared[j].id + "'>";
                                htm += "  <a href='" + response[i].permalink + "' title='" + response[i].title + "'>";
                                htm += "      <div class='building-info'>";
                                htm += "          <img src=" + response[i].compared[j].image + ">";
                                htm += "          <h2>Приміщення " + response[i].name_house + "</h2>";
                                htm += "          <span class='cat-name'>" + response[i].tax_name + "</span>";
                                htm += "          <div class='content d-flex wrap justify-content-space'>";
                                htm += "          <p>К-сть кімнат: " + response[i].compared[j].rooms + "</p>";
                                htm += "           <p>Площа: " + response[i].compared[j].square + "</p>";
                                htm += "           <p>Балкон: <span>" + response[i].compared[j].balcony['label'] + "</span></p>";
                                htm += "          <p>Санвузол: " + response[i].compared[j].bedroom['label'] + "</p>";
                                htm += "      </div>";
                                htm += "      </div>";
                                htm += "  </a>";
                                htm += "</li>";
                            mainBox.append(htm);
        
                            }
                            var html  = "<li id='real-estate-" + response[i].id + "'>";
                            html += "  <a href='" + response[i].permalink + "' title='" + response[i].title + "'>";
                            html += "      <div class='building-info'>";
                            html += "          " + response[i].image + " ";
                            html += "          <h2>Будинок " + response[i].name_house + "</h2>";
                            html += "          <span class='cat-name'>" + response[i].tax_name + "</span>";
                            html += "          <div class='content d-flex wrap justify-content-space'>";
                            html += "          <p>К-сть поверхів: " + response[i].floors['value'] + "</p>";
                            html += "          <p>Координати: " + response[i].coordinate + "</p>";
                            html += "          <p>Тип: " + response[i].type_house + "</p>";
                            html += "          <p>Екологічність: " + response[i].ecology['label'] + "</p>";
                            html += "      </div>";
                            html += "      </div>";
                            html += "  </a>";
                            html += "</li>";
                        mainBox.append(html);
        
                        }
                       
                        }
                       
                mainBox.animate({opacity: 1}, 300);
                var items = $("#ajax_filter_search_results li");
                var numItems = items.length;
                var perPage = 5;
                console.log(numItems);
                items.slice(perPage).hide();
                $('.pagination-block').pagination({
                    items: numItems,
                    itemsOnPage: perPage,
                    prevText: false,
                    nextText: false,
                    onPageClick: function (pageNumber) {
                        var showFrom = perPage * (pageNumber - 1);
                        var showTo = showFrom + perPage;
                        items.hide().slice(showFrom, showTo).show();
                    }
                });
            },
            error: function (xhr, errorThrown) {
                mainBox.empty();
                var html = "<p class='error'>Жодних співпадінь не знайдено, спробуйте інший запит.</p>";
                mainBox.append(html);
            }
        });
       
});


  
});

