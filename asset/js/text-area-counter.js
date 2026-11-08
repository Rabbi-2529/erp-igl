//--Start Recipient--
function count_textarea(textarea_location) {
    $(textarea_location+' .count_me').textareaCount({
        'maxCharacterSize': 765,
        'textAlign': 'right',
        'warningColor': '#CC3300',
        'warningNumber': 160,
        'isCharacterCount': true,
        'isWordCount': false,
        'displayFormat': '#input Characters | #left Characters Left',
        'originalStyle': 'contacts-count',
        'counterCssClass': textarea_location+' .charleft',

    }, function (data) {
        var parts = 1;
        // noinspection NestedFunctionCallJS
        var isUnicode = isDoubleByte($(textarea_location+' .count_me').val());
        var typeRadio = $(textarea_location+' input:radio[name=recipientsmsRadios]:checked').val();
        var charPerSMS = 160;
        if (isUnicode) {
            charPerSMS = 70;
            if (data.input > 70) {
                parts = Math.ceil(data.input / 67);
                charPerSMS = 67;
            }
            if (typeRadio == "text") {
                $(textarea_location+" #recipientsmsRadiosUnicode").prop('checked', true);
            } else if (typeRadio == "flash") {
                $(textarea_location+ "#recipientsmsRadiosUnicodeFlash").prop('checked', true);
            }else{
                $(textarea_location+" #recipientsmsRadiosUnicode").prop('checked', true);
            }

        }
        else {
            // noinspection NestedFunctionCallJS
            var isUnicodeNormal = isDoubleByteNormal($(textarea_location+' .count_me').val());
            if (isUnicodeNormal) {
                charPerSMS = 140;
                if (data.input > 140) {
                    parts = Math.ceil(data.input / 134);
                    charPerSMS = 134;
                }
            } else {
                charPerSMS = 160;
                if (data.input > 160) {
                    parts = Math.ceil(data.input / 153);
                    charPerSMS = 153;
                }
            }

            if (typeRadio == "unicode") {
                $(textarea_location+" #recipientsmsRadiosText").prop('checked', true);
            } else if (typeRadio == "flashunicode") {
                $(textarea_location+" #recipientsmsRadiosFlash").prop('checked', true);
            }else{
                $(textarea_location+" #recipientsmsRadiosText").prop('checked', true);
            }
        }
        $(textarea_location+'  .parts-count').text('| ' + parts + ' SMS (' + charPerSMS + ' Char./SMS)');
    });
}
// <!-- End Recipient-->

/*check unicode or not*/
function isDoubleByte(str) {
    for (var i = 0, n = str.length; i < n; i++) {
        //if (str.charCodeAt( i ) > 255 && str.charCodeAt( i )!== 8364 )
        if (str.charCodeAt(i) > 255) {
            return true;
        }
    }
    return false;
}

function isDoubleByteNormal(str) {
    for (var i = 0, n = str.length; i < n; i++) {
        if (str.charCodeAt( i ) ==91
            || str.charCodeAt( i ) ==92
            || str.charCodeAt( i ) ==93
            || str.charCodeAt( i ) ==94
            || str.charCodeAt( i ) ==123
            || str.charCodeAt( i ) ==124
            || str.charCodeAt( i ) ==125
            || str.charCodeAt( i ) ==126
        ) { return true; }
    }
    return false;
}