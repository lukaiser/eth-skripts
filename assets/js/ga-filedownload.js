jQuery(document).ready(function($) {
    var baseHref = "";
    if (jQuery("base").attr("href") != undefined)
        baseHref = jQuery("base").attr("href");
    //add Google Analytics to Book files
    jQuery(".alt-formats a").each(function() {
        var href = jQuery(this).attr("href");
        if (href) {
            jQuery(this).click(function() {
                var filePath = jQuery(this).attr("href");
                var filename = decodeURIComponent((new RegExp("[?|&]filename=" + "([^&;]+?)(&|#|;|$)").exec(filePath)||[,""])[1].replace(/\+/g, "%20"))||null;
                var type = decodeURIComponent((new RegExp("[?|&]type=" + "([^&;]+?)(&|#|;|$)").exec(filePath)||[,""])[1].replace(/\+/g, "%20"))||null;
                _gaq.push(["_trackEvent", "Download", type+"", filename+""]);

                if (jQuery(this).attr("target") != undefined && jQuery(this).attr("target").toLowerCase() != "_blank") {
                    setTimeout(function() { location.href = baseHref + href; }, 200);
                    return false;
                }
            });
        }
    });
});
