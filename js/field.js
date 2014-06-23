jQuery(function($) {

    $(document).ready(function() {
        $(".js-select2").each(function(i, select) {
            var id = $(select).attr('id');
            var data = [];

            $(select).find("option").each(function(i, option){
                var id = $(option).val();
                var value = $(option).text();
                data.push({id: id, text: value});
            })

            $("#js-select2-"+id).select2({
                createSearchChoice: function(term, data) {

                    if ($(data).filter( function() { return this.text.localeCompare(term)===0;
                    }).length===0) {
                        return {id:term, text:term};
                    }

                },
                initSelection : function (element, callback) {
                    var data = [];
                    $(".js-select2 [selected='selected']").each(function(i, selected){
                        var id = $(selected).val();
                        var value = $(selected).text();
                        data.push({id: id, text: value});
                    });

                    callback(data);
                },
                multiple: true,
                width : "element",
                data: data
            });
        });
    })
})