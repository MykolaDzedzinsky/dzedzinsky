    $.getJSON('/api/products', function (data) {
        $('#search').keyup(function () {
            var searchField = $('#search').val();
            var myExp = new RegExp(searchField, "i");
            var output = '<ul>';
            $.each(data, function (key, val) {
                if ((val.Name.search(myExp) != -1) || (val.About.search(myExp) != -1)) {
                    output += '<li>';
                    output += '<h2>' + val.Name + ' $ ' + val.Price + '</h2>';
                    output += '<img src="/Content/images/' + val.ShortName + '.jpg" alt="" />';
                    output += '<p>' + val.About + '</p>';
                    output += '</li>';
                }
            });
            output += '</ul>';
            $('#update').html(output);
        })
    })
