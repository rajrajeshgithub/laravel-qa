

/////////////////////////////////////////////////////////

//------------------Email Template PAGE--------------
/*tinymce.init(
{
    selector: "textarea#description",
    theme: "modern",
    height: 300,
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class]",
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });
 
 tinymce.init(
{
    selector: "textarea#mailBody",
    theme: "modern",
    height: 300,
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class]",
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });*/
 

 tinymce.init({
    selector: "textarea#mailBody",
    theme: "modern",
	content_css : FRONTURL+"app/views/css/donasity.css,"+FRONTURL+"app/views/css/font-awesome.min.css,"+FRONTURL+"app/views/css/common.css,"+FRONTURL+"app/views/css/pageCommon.css"+FRONTURL+"app/views/css/responsive.css",	
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor colorpicker textpattern"
    ],
    toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
    toolbar2: "print preview media | forecolor backcolor emoticons",
    image_advtab: true,
    templates: [
        {title: 'Test template 1', content: 'Test 1'},
        {title: 'Test template 2', content: 'Test 2'}
    ]
});
 
 
  tinymce.init(
{
    selector: "textarea#mailBodySpanish",
    theme: "modern",
    height: 300,
	content_css : FRONTURL+"app/views/css/donasity.css,"+FRONTURL+"app/views/css/font-awesome.min.css,"+FRONTURL+"app/views/css/common.css,"+FRONTURL+"app/views/css/pageCommon.css"+FRONTURL+"app/views/css/responsive.css",	
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class],a[href|class|id]",
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });
 
 
 tinymce.init(
{
    selector: "textarea#LandingPageHTML",	
    theme: "modern", 
    height: 300,
	content_css : FRONTURL+"app/views/css/donasity.css,"+FRONTURL+"app/views/css/font-awesome.min.css,"+FRONTURL+"app/views/css/common.css,"+FRONTURL+"app/views/css/pageCommon.css"+FRONTURL+"app/views/css/responsive.css",	
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class|title],a[href|class|id|title],form[action|method|id]",
   valid_children : "+body[style],span[i|a],a[div|img|i]",
   valid_elements: "*[*]",
   schema: "html5",
   visualblocks_default_state: true,
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });
 
 
 
 tinymce.init(
{
    selector: "textarea#SP_landingpageHtml",	
    theme: "modern", 
    height: 300,
	content_css : FRONTURL+"app/views/css/donasity.css,"+FRONTURL+"app/views/css/font-awesome.min.css,"+FRONTURL+"app/views/css/common.css,"+FRONTURL+"app/views/css/pageCommon.css"+FRONTURL+"app/views/css/responsive.css",	
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class|title],a[href|class|id|title],form[action|method|id]",
   valid_children : "+body[style],span[i|a],a[div|img|i]",
   valid_elements: "*[*]",
   schema: "html5",
   visualblocks_default_state: true,
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });


/*tinyMCE.init({
        // General options
		selector: "textarea#LandingPageHTML",
        mode : "textareas",
        theme : "modern",
      // plugins : "visualblocks,inlinepopups",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,styleselect,justifyleft,justifycenter,justifyright,justifyfull,|,visualblocks,code",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
       // content_css : "/js/tinymce_3_x/examples/css/content.css",
        visualblocks_default_state: true,

        // Schema is HTML5 instead of default HTML4
        schema: "html5",

        // End container block element when pressing enter inside an empty block
        end_container_on_empty_block: true,

        // HTML5 formats
        style_formats : [
                {title : 'h1', block : 'h1'},
                {title : 'h2', block : 'h2'},
                {title : 'h3', block : 'h3'},
                {title : 'h4', block : 'h4'},
                {title : 'h5', block : 'h5'},
                {title : 'h6', block : 'h6'},
                {title : 'p', block : 'p'},
                {title : 'div', block : 'div'},
                {title : 'pre', block : 'pre'},
                {title : 'section', block : 'section', wrapper: true, merge_siblings: false},
                {title : 'article', block : 'article', wrapper: true, merge_siblings: false},
                {title : 'blockquote', block : 'blockquote', wrapper: true},
                {title : 'hgroup', block : 'hgroup', wrapper: true},
                {title : 'aside', block : 'aside', wrapper: true},
                {title : 'figure', block : 'figure', wrapper: true}
        ]
});

*/

 tinymce.init(
{
    selector: "textarea#longdeschtml",
    theme: "modern",
    height: 300,
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class],a[href|class|id]",
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });
 
  tinymce.init(
{
    selector: "textarea#userbio",
    theme: "modern",
    height: 300,
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor"
    ],
    toolbar1: "insertfile undo redo | styleselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright alignjustify | link unlink anchor | forecolor backcolor emoticons | bullist numlist outdent indent | image media | preview code ",
   image_advtab: true,
   toolbar_items_size: 'small',
   extended_valid_elements : "span[class]",
   convert_urls: false,
   style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ]
 });








//-------------------END------------------
 
 
