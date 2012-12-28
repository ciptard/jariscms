<?php 
$base_url = $_REQUEST["base_url"]; 
if($base_url == "/")
{
    $base_url = "";
}
?>
//
//  Configuration
//

$(document).ready(function(){
    $(".lightbox").lightbox({
        fileLoadingImage : '<?php print $base_url ?>/modules/jquery_lightbox/lightbox/images/loading.gif',
        fileBottomNavCloseImage : '<?php print $base_url ?>/modules/jquery_lightbox/lightbox/images/close.gif',
        strings : {
            help: '',
            prevLinkTitle: '',
            nextLinkTitle: '',
            prevLinkText:  '&laquo;',
            nextLinkText:  '&raquo;',
            closeTitle: '',
            image: '',
            of: ' / '
        }
    });
});