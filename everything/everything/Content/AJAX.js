$.getJSON('/api/products', function (data) {
    var output = '<ul>';
    $.each(data, function(key, val) {
        output += '<li>' + val.Name + '</li>';
    });
    output += '</ul>';
    $('.update').append(output);
})