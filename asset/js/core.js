


window.expCore = function () {
    var $ = jQuery ;

    this._default = {

        domain : window.location.protocol +'//'+ window.location.host ,
        urlLib : '/libraries/GNZ11/assets/js/gnz11.js' ,
        elementBtn : "exsel_products-btn" ,

    } ;
    var self = this ;
    this.Init = function () {

        var expCoreSetting = Joomla.getOptions('expCoreSetting') ;
        this._default = $.extend(true , this._default, expCoreSetting );

        Joomla.loadOptions({
            siteUrl: this._default.domain,
            siteUrlsiteUrl : this._default.domain,
        });


        var url = this._default.domain + this._default.urlLib ;
        this._loadJsFile( url , this._getloadFile);

        console.log( this._default )
    }
    this._getloadFile = function () {

        wgnz11.__loadModul.Fancybox().then(function (a) {
            a.open( {
                type : 'iframe' ,
                src : self._default.domain +'/libraries/GNZ11/assets/js/plugins/jQuery/file_upload/index.html' ,
                baseClass: ' Upload_Modal' ,
                touch : false ,
                height : '600',

                'max-height' : '80%',
                iframe : {
                    css : {
                        width  : '800px',
                        height : '90vh',
                    }
                },
                beforeShow  : function (instance, current){

                },
                afterShow   : function(instance, current)   {

                },

                afterClose  : function () { },
            });
        });

        console.log(  )
        console.log( this )
    }

    this._loadJsFile = function(url, callback ){
        var script = document.createElement("script")
        script.type = "text/javascript";
        if (script.readyState){  //IE
            script.onreadystatechange = function(){
                if (script.readyState == "loaded" ||
                    script.readyState == "complete"){
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //Others
            script.onload = function(){
                callback();
            };
        }
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }

};

(function () {
    var exp = new window.expCore();
    exp.Init();
})();