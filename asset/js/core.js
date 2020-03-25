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
        this._loadJsFile( url , this._getloadFile );

        console.log( this._default )
    };
    /**
     * Загруженный файл EXSEL
     */
    this.workbook ;
    /**
     * Загрузить Файл EXSEL
     * @param urlFile
     */
    this.loadXlsxFile = function (urlFile) {
        var oReq = new XMLHttpRequest();
        oReq.open("GET", urlFile, true);
        oReq.responseType = "arraybuffer";
        oReq.onload = function(e) {
            $(self.$LoaderFile).text('File analysis');
            var arraybuffer = oReq.response;
            /* convert data to binary string */
            var data = new Uint8Array(arraybuffer);
            var arr = new Array();
            for (var i = 0; i !== data.length; ++i) arr[i] = String.fromCharCode(data[i]);
            var bstr = arr.join("");
            setTimeout(function () {
               /* Call XLSX */
                window.workbook = XLSX.read(bstr, {
                    type: "binary"
                });
                // Удалить индикатор загрузка - анализ
                self.removeLoaderFile();
                self.getSheetsData();
            }, 1000 );
        };
        oReq.send();
    };


    // Номер Листа в книги
    window.CurrentSheet = 0 ;
    // Текущая строка
    window.CurrentRow = 0 ;
    // Количество Строк в документ
    window.worksheetCount = 0  ;
    // Статистика обработки Exsel файла
    window.ExselStatistic = {
        // Обработано строк
        RowsProcessed : 0 ,
        // Обновлено товаров
        ProductUpdate : 0 ,
        // Название текущего листа
        SheetName : null ,
    };
    /**
     * Парковка перебора документа
     * @constructor
     */
    window.ExselHadlerStoped = function(){
        var $TableRow = $(self.$rowFormFile);
        // Номер текущего листа
        window.CurrentSheet = 0
        // Текущая строка в листе
        window.CurrentRow = 0 ;
        // Количество Строк в документ
        window.worksheetCount = 0  ;

        // Удалить строку с татистикой
        $TableRow.closest('table').find('.statics-line').remove();

        console.log('@window ExselStatistic Обработано строк всего: ', window.ExselStatistic.RowsProcessed )
        // Бросить счетчик обработаннх строк
        window.ExselStatistic.RowsProcessed = 0 ;
        window.ExselStatistic.ProductUpdate = 0 ;
    };
    /**
     * Отображение статистики
     * @param nameSheets
     * @constructor
     */
    window.StaticSaveData = function(nameSheets){
        var $TableRow = $(self.$rowFormFile);
        var $parentTr = $TableRow.closest('tr').next();
        var $el = $('<tr />' , {class : 'statics-line'});
        $el.append($('<td />'));
        $el.append($('<td />',{html : 'Текущий лист: <b>'+ window.ExselStatistic.SheetName +'</b>' ,}));
        $el.append($('<td />',{html : 'Обработанно строк: <b>'+ window.ExselStatistic.RowsProcessed +'</b>' ,}));
        $el.append($('<td />',{html : 'Обновлено товаров: <b>'+ window.ExselStatistic.ProductUpdate +'</b>' ,}));
        $TableRow.closest('table').find('.statics-line').remove();
        $parentTr.after($el);
    };
    /**
     * Подготовить данный перед отправкой
     * @return {undefined}
     */
    window.SaveData = function () {
        // Данные для отправки на сервер
        var SendData = [] ;
        // столбцы для отбора
        var column = window.fileUploadCoreSetting.worksheet.columnSlug
        // получить Название листа по индексу
        window.ExselStatistic.SheetName = workbook.SheetNames[window.CurrentSheet];
        // Получить лист в книге по имени
        var worksheet = window.workbook.Sheets[window.ExselStatistic.SheetName];
        console.log( '@window sheet_name(Название текущего листа)' , window.ExselStatistic.SheetName );

        // Количество Строк в документ
        if (!window.worksheetCount) {
            var arrayOfStrings = worksheet['!ref'].split(':');
            var maxRowSheet = +arrayOfStrings[1].replace(/[^\d;]/g, '')
            console.log( '@window workbook maxRowSheet', maxRowSheet ) ;
            window.worksheetCount =  maxRowSheet ;
        }

        var step = window.fileUploadCoreSetting.worksheet.step ;
        var stepStart = window.CurrentRow ;
        var maxRow = stepStart+step ;

        // Перебор строк
        for (var i = stepStart; i < maxRow; i++) {
            var arrVal = {};
            var cell , ind  ;
            var resetRow = false ;
            // Перебор колонок в строке
            $.each( window.fileUploadCoreSetting.worksheet.columnSlug , function ( ii , a ) {
                ind = ii+i ;
                cell =  worksheet[ind];
                // Если данных в обязательных ячейках нет
                if ( typeof cell === 'undefined' || resetRow ) {
                    if ( maxRow < window.worksheetCount ) {
                        maxRow++;
                    }
                    resetRow = true ;
                    return false ;
                }else{
                    arrVal[a] = cell.v ;
                }
            });
            if ( !resetRow ) {
                // Добавить в массив для отправеи на сервер
                SendData.push(arrVal);
                // Статистика - Количество обработанных строк
                window.ExselStatistic.RowsProcessed++;
                window.StaticSaveData();
            }
        }
        // текущая строка
        window.CurrentRow = i ;
        /**
         * если в листе еще остались строки
         */
        if ( window.CurrentRow <= window.worksheetCount ) {
            console.log('@window worksheetCount(Общее количество строк в листе)', window.worksheetCount);
            console.log('@window CurrentRow(Текущая строка)', window.CurrentRow);
            if (self.DEBAG) console.log('@window SendData(Отобранные строки для обновления)', SendData);
            // Отправка данных на сервер Для обновление цен
            self.SendAjaxData(SendData);
        }
        else{
            if(self.DEBAG) console.log( '@window@SaveData--(Осталось Листов в книге)', workbook.SheetNames.length - window.CurrentSheet  );
            // Переключаем на следующий лист
            window.CurrentSheet++;

            console.log( '@window CurrentSheet(Номер открытого листа в книге)' , window.CurrentSheet );
            console.log( '@window workbook.SheetNames(Количество листов ) ' , workbook.SheetNames.length );

            // Текущая строка в листе
            window.CurrentRow = 0 ;
            // Количество Строк в документ
            window.worksheetCount = 0  ;

            console.log( '@window worksheetCount(Общее количество строк в листе)' , window.worksheetCount );
            console.log( '@window CurrentRow(Текущая строка в листе)', window.CurrentRow );
            console.log('@window SendData(Отобранные строки для обновления)',SendData);
            // Отправка данных на сервер Для обновление цен
            self.SendAjaxData( SendData ) ;

        }
    };
    /**
     * Отправка данных на сервер Для обновление цен
     * @constructor
     */
    this.SendAjaxData = function (SendData) {
        var Plugin = window.fileUploadCoreSetting.Plugin ;
        window.fileUploadCoreSetting.Plugin.method = "addFilePrice"
        if( !SendData.length ){
            window.SaveData() ;
            return ;
        }
        $.ajax({
            method : 'POST',
            dataType: 'json',
            url: 'index.php?option=com_ajax&format=json'
                +'&plugin='+Plugin.name
                +'&group='+Plugin.gorup ,
            data: {
                Setting : window.fileUploadCoreSetting ,
                SendData : SendData ,
            } ,
            success : function (data) {
                if(data.success){
                    if (typeof data.data[0].updateProductRow !== 'undefined'){
                        window.ExselStatistic.ProductUpdate = window.ExselStatistic.ProductUpdate + data.data[0].updateProductRow
                    }

                }
                // если лист не существует
                if ( !(window.workbook.SheetNames.length - window.CurrentSheet)  ) {
                    // Паркуем скрипт
                    window.ExselHadlerStoped();
                    return ;
                }
                window.SaveData() ;
            }
        });
    };
    /**
     * Получить данные о листах
     * Вставить строку с кнопкой обработать
     */
    this.getSheetsData = function () {
        var $row = $(self.$rowFormFile);

        $row.after('<tr class="porcess-row-btns">' +
            '<td></td>' +
            '<td>Найдено:</td>' +
            '<td>Листов: '+ window.workbook.SheetNames.length +'</td>' +
            '<td>' +
                '<button type="button" onclick="window.parent.SaveData();" class="btn btn-success">\n' +
                '<i class="glyphicon glyphicon-list-alt"></i>\n' +
            '              <span>Обработать</span>\n' +
            '    </button></td>' +
        '</tr>') ;
    };
    /**
     * Установка цен
     * @param event
     * @private
     */
    this._setPrice = function (event) {
        event.preventDefault() ;
        var expCoreSetting = Joomla.getOptions('expCoreSetting') ;
        var filename = $(this).attr('filename');

        var fileUrl = window.fileUploadCoreSetting.upload_url + filename ;
        self.addLoaderFile( this , 'File upload' );
        // Прочитать Exsel File
        self.loadXlsxFile( fileUrl );


    };
    this.$rowFormFile ;
    this.$LoaderFile ;
    this.removeLoaderFile = function () {
        var $row = $(self.$rowFormFile);
        $row.find('.btn.btn-primary.start').removeAttr('disabled');
        self.$LoaderFile.remove();
    };
    this.addLoaderFile = function ( el , text ) {
        self.$rowFormFile = $(el).closest('tr').addClass('xlsx-process');
        var $parent = $(el).closest('td');
        this.$LoaderFile = $('<div />',{
            id : 'LoaderFile',
            class : 'fileRead' ,
            text : text
        })
        $parent.append(  this.$LoaderFile );
    };
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
                beforeShow  : function (instance, current){ },
                afterShow   : function(instance, current)   {
                    var dataTemp ;
                    self._loadJsFile('https://oss.sheetjs.com/sheetjs/xlsx.full.min.js' /*, self.JsParseXlsxFile */) ;
                    window.fileuploadstopped = function (e, data , $fileupload ) {
                        var $ = jQuery ;
                        console.log( $fileupload  )
                        var $file_up_loadForm = $fileupload ;

                        var resultName = dataTemp.result.files[0].name;
                        var $newFile = $file_up_loadForm.find('[download="'+resultName+'"]');
                        var $tr = $newFile.closest('tr');
                        var $btnDelite = $tr.find('[data-type="DELETE"]');
                        var $btnParent = $btnDelite.parent();
                        // Создать Кнопку обработка файла
                        $btnParent.prepend($('<button />' , {
                            class : 'btn btn-primary start' ,
                            html : '<span class="glyphicon glyphicon-check"></span>',
                            title : 'Установить цены' ,
                            click : self._setPrice ,
                            attr : {
                                filename :  resultName ,
                            }
                        }));
                    };
                    window.fileuploaddone = function (e, data) {
                        if (typeof data === 'undefined' ) return ;
                        var $ = jQuery ;
                        dataTemp = {
                            result : data.result
                        } ;
                        var $file_up_loadForm = $(window.file_up_loadForm) ;
                        var resultName = data.result.files[0].name;
                        var newFile = $file_up_loadForm.find('[download="'+resultName+'"]')

                    };





                },
                afterClose  : function () { },
            });
        });
        console.log( this )
    };
    this.JsParseXlsxFile = function () {};
    /**
     * Загрузка JS файлов
     * @param url - файл
     * @param callback - после загрузки файла
     * @private
     */
    this._loadJsFile = function(url, callback ){
        var script = document.createElement("script")
        script.type = "text/javascript";
        if (typeof callback === 'function'){
            if (script.readyState){  //IE
                script.onreadystatechange = function(){
                    if (script.readyState === "loaded" || script.readyState === "complete"){
                        script.onreadystatechange = null;
                        callback();
                    }
                };
            } else {  //Others
                script.onload = function(){ callback();  };
            }
        }

        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }

    window.fileuploaddestroyed = function(e, data , $fileupload  ){
        $fileupload.find('tr.porcess-row-btns').remove();
        $fileupload.closest('div.container').find('button.exit').remove() ;
        console.log( e )
        console.log( data )
        console.log(  )
    };
};


(function () {
    var exp = new window.expCore();
    exp.Init();
})();





/*
window.file_up_loadForm ;
function childLoaded() {window.file_up_loadForm = childGetElementById('fileupload');}*/
