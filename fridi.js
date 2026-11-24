
jQuery(document).ready(function($){

    jQuery(".page tr.product:not(.promo) .count span.pointer:not(.readonly)").click(function (e) {
        plusMinusCountProduct(this)
    })
    jQuery('form input').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    registerToCalculatePrice();

    jQuery("svg.file-upload").on("click",function (){
        $(this).closest('form').find('.input-label').removeClass('hidden');
        $(this).closest('form').addClass("margin-after-10");
    })
    jQuery("select[name=supplier_id]").on("change",function (){
        $(this).closest('form').find('input[type=file]').click();
    })

    jQuery('input[type="file"][name="bills"]').on('change', function () {
        if (this.files.length > 0) {
            $(this).closest('form').submit();
        }
    });
});

// function fillClientPriceModal(result) {
//     if (result.array.length > 0) {
//         jQuery('#update_client_price input[name=id]').val(result.array[0].id);
//         //jQuery('#update_client_price input[name=client_id]').val(result.client_id);
//         jQuery('#update_client_price input[name=client_price]').val(result.array[0].client_price);
//     }
//     else{
//         jQuery('#update_client_price input[name=id]').val("");
//         jQuery('#update_client_price input[name=client_price]').val("");
//     }
// }


function import_excel_done(form,data){
    //jQuery(".archive-table").closest(".dt-container ").remove();
    if(data.rows) {
        jQuery(".page .archive-table tbody").prepend(data.rows);
        //setDataTable();
    }
    show_slider_message(data.message);
    //alert(data.msg);
}

function choose_supplier_column_mapping(form, data){
    jQuery('#supplier_column_mapping_modal').find('input[name=supplier_id]').val(data.supplier_id);
    jQuery('#supplier_column_mapping_modal').find('table.excel-rows').html(data.excel_rows);
    jQuery('#supplier_column_mapping_modal').find('select').html(data.columns_options);

    openModal("#supplier_column_mapping_modal");
}

function import_from_xlsx(form, data){
    closeModal();
    jQuery('#importCollection').submit();
}

function filterOrderProdoctsRowsToSave() {
    //גם שורות שמוסתרות בחיפוש/סינון - הן נשלחות בשמירה (hiddenOrderRowsFields), ושורה ריקה לא צריכה להישלח
    var rows = jQuery('tr').add(orderRows()).filter(function () {
        var $row = jQuery(this);

        return jQuery.trim($row.find('td.count input').val() || '') === '' &&
            jQuery.trim($row.find('td.id input').val() || '') === '';
    });
    rows.find('td input').prop('disabled', true);
}

//מבצע 11+1 (סוג 3): כשבהזמנה יש לפחות 4 ארגזים שלמים מהמוצרים של אותו מבצע (ביחד, גם מכמה מוצרים) -
//בכל שורה בקבוק אחד מתנה על כל ארגז שלם (משלמים על 11 מתוך 12), מתוך ההזמנה. שורה שהוזמנה בבודדים לא נחשבת
const ELEVEN_PLUS_ONE_MIN_BOXES = 4;
const ELEVEN_PLUS_ONE_GROUP = 12;
var elevenPlusOneRefreshing = false;

//מבצע ה-11+1 של המוצר (null אם אין)
function getElevenPlusOnePromotion(product) {
    if (typeof promotionsByProduct === 'undefined') {//לא בעמוד הזמנה
        return null;
    }
    var product_id = parseInt(product.find(".product_id span").text() || 0);
    var byProduct = (promotionsByProduct[product_id] ?? []).find(pro => pro.type == "3");
    if (byProduct) {
        return byProduct;
    }
    //מבצע לספק בלי רשימת מוצרים - חל על כל המוצרים של הספק
    var supplier_id = parseInt(product.find(".supplier_id span").text() || 0);
    return (promotionsBySupplier[supplier_id] ?? []).find(pro => pro.type == "3") || null;
}

//ארגזים שלמים בשורה שנחשבים למבצע: מוצר של 12 בקבוקים בארגז, שהוזמן בארגזים (לא בונוס/מבצע)
function elevenPlusOneBoxes(product) {
    if (product.hasClass('bonus') || product.hasClass('promo')) {
        return 0;
    }
    if ((parseInt(product.find('.units_in_box span').text()) || 0) != ELEVEN_PLUS_ONE_GROUP) {
        return 0;
    }
    if (product.find(".order_individual input").val() != "0") {
        return 0;
    }
    return parseInt(product.find('.count input').val()) || 0;
}

//כל השורות בהזמנה של מוצרים מאותו מבצע - גם שורות שמוסתרות בחיפוש/סינון (orderRows)
function elevenPlusOneRows(promotion) {
    return orderRows().filter(function () {
        var row = jQuery(this);
        if (row.hasClass('bonus') || row.hasClass('promo')) {
            return false;
        }
        var pro = getElevenPlusOnePromotion(row);
        return pro && pro.id == promotion.id;
    });
}

//כותב את ההנחה בשורת המוצר ומחזיר את מספר הבקבוקים לזיכוי (0 אם המבצע לא חל)
function applyElevenPlusOne(product, units) {
    if (product.hasClass('bonus') || product.hasClass('promo')) {
        return 0;
    }
    var promotion = getElevenPlusOnePromotion(product);
    if (!promotion) {
        return 0;
    }
    if ((parseInt(product.find('.units_in_box span').text()) || 0) != ELEVEN_PLUS_ONE_GROUP) {//המבצע רק על מוצרים של 12 בקבוקים בארגז
        return 0;
    }
    var rows = elevenPlusOneRows(promotion);
    var totalBoxes = 0;
    rows.each(function () {
        totalBoxes += elevenPlusOneBoxes(jQuery(this));
    });
    //מ-4 ארגזים ומעלה (ביחד) - בקבוק על כל ארגז שלם בשורה
    var freeUnits = totalBoxes >= ELEVEN_PLUS_ONE_MIN_BOXES ? elevenPlusOneBoxes(product) : 0;
    var discountInput = product.find('.discount_percent input');
    if (freeUnits > 0 && units > 0) {
        discountInput.autoNumeric('set', (freeUnits * 100 / units).toFixed(2));
    } else {
        //מתחת לסף - ההנחה של המבצע מתבטלת
        freeUnits = 0;
        discountInput.val("");
    }
    //הסף משותף לכל מוצרי המבצע - שינוי בשורה אחת יכול להפעיל או לבטל את המבצע בשורות האחרות.
    //הדגל מונע לולאה: החישוב של השורות האחרות לא מחשב שוב את כולן
    if (!elevenPlusOneRefreshing) {
        elevenPlusOneRefreshing = true;
        try {
            rows.not(product).each(function () {
                var row = jQuery(this);
                if ((parseInt(row.find('.count input').val()) || 0) > 0) {
                    calculatePrice(row);
                }
            });
        } finally {
            elevenPlusOneRefreshing = false;
        }
    }
    return freeUnits;
}

function checkPromotions(product, currentValue){
    var sourceKey = product.find(".sort-column").text();
    var supplier_id = parseInt(product.find(".supplier_id span").text() || 0);
    var product_id = parseInt(product.find(".product_id span").text() || 0);

    var relevant_promotions = (promotionsByProduct[product_id] ?? []).slice();
    if (promotionsBySupplier[supplier_id]) {
        var promotion_Supplier = promotionsBySupplier[supplier_id].slice()
        promotion_Supplier.forEach(pro_s => {
            relevant_promotions.push(pro_s );
        });

    }
    var currentCount = countUnitsForProduct(product, currentValue);

    relevant_promotions.forEach(pro => {
        var needToBuy = parseInt(pro.buy);
        var countToGet = parseInt(pro.get);
        var product_get = (pro.type == "3" ? product_id : pro.product_get);
        var productPromo = orderRows().filter(".promo").has("td.product_id input[type=hidden][value=" + product_get + "]").first();
        switch (pro.type) {
            case "1": //  קנה קבל
              /*  if(currentValue ==0){
                    jQuery("tr.promo:has(.product_id input[type=hidden][value=" + pro.product_get + "])").eq(0)
                        .find(".count input").val(0);
                    jQuery("tr.promo:has(.product_id input[type=hidden][value=" + pro.product_get + "])").eq(0).hide();
                    return;
                }*/

                var countProductsInOrder = 0;
                var productsInSpecial = null;
                if (pro.products_buy ) {
                     productsInSpecial = JSON.parse(pro.products_buy);
                }
                if(Array.isArray(productsInSpecial)) {
                        productsInSpecial.forEach(productId => {
                            orderRows().filter(".in-cart:not(.bonus,.promo)").has(".product_id input[type=hidden][value=" + productId + "]").each(function (k, p) {
                                countProductsInOrder += countUnitsForProduct(p);
                                //countProductsInOrder += parseInt(jQuery(p).find(".count input").val());
                            });
                        });
                }
                else {
                    orderRows().filter(".in-cart:not(.bonus,.promo)").each(function () {
                        const row = jQuery(this);

                        if (row.find("td.supplier_id span").text().trim() !== pro.supplier_id) {
                            return;
                        }
                        countProductsInOrder += countUnitsForProduct(row);//parseInt(p.find(".count input").val());
                    });
                }
                var countPromotions = getCountPromotions(pro.product_get);
                var productToGet = orderRows().filter(":not(.bonus,.promo)").has("td.product_id input[type=hidden][value=" + pro.product_get + "]").first();

                    var toAdd = countToGet * parseInt(countProductsInOrder / needToBuy);

                    if (countPromotions == 0 && productPromo.length==0) {// קורה כי הכמות לארגז לא מדויקת לכמות המבצע
                        if(toAdd >0) addProdoctBonus(productToGet, toAdd,sourceKey);
                    } else {
                        productPromo.find(".count input").val(toAdd);
                        if(toAdd ==0){  productPromo.hide();}
                        else {productPromo.show();}
                    }

                break;
            case "2": //  קנה מעל
                var priceToSupplier = 0;

                orderRows().filter(".in-cart:not(.bonus,.promo)").each(function () {
                    const row = jQuery(this);

                    if (row.find("td.supplier_id span").text().trim() !== pro.supplier_id) {
                        return;
                    }
                    priceToSupplier += parseFloat(row.find("td.total input").autoNumeric('get')) || 0;
                });
                var countPromotions = getCountPromotions(pro.product_get);
                if (priceToSupplier >= parseFloat( pro.price_more)) {

                    if (pro.get && countPromotions == 0 ) {
                        if( productPromo.length>0){
                            productPromo.find(".count input").val(countToGet);
                            productPromo.show();
                        }
                        else {
                            var productToGet = orderRows().filter(":not(.bonus,.promo)").has("td.product_id input[type=hidden][value=" + pro.product_get + "]").first();
                            //var productToGet = jQuery("tr:not(.bonus,.promo):has(.product_id input[type=hidden][value=" + pro.product_get + "])");
                            addProdoctBonus(productToGet, countToGet, sourceKey);
                        }
                        break;
                    }

                    if (pro.discount) {//
                        jQuery("input[name=discount]").autoNumeric('set', pro.discount);
                        calculateForPayment(jQuery("input[name=total]").val());
                    /*    var totalOrder = jQuery("input[name=total]").val();
                        jQuery("input[name=total]").autoNumeric('set', totalOrder * (100 - parseFloat(pro.discount)) / 100)
*/
                    }
                }
                else{
                    if (pro.get && countPromotions > 0) {
                        productPromo.find(".count input").val(0);
                        productPromo.hide();

                        //removeProdoctFromOrder(productToGet)
                        break;
                    }

                    if (pro.discount) {
                        jQuery("input[name=discount]").autoNumeric('set', '');
                        calculateForPayment(jQuery("input[name=total]").val());
                    }
                }
                break;
            case "3": //  11+1 - ההנחה נכתבת בשורת המוצר ב-calculatePrice (applyElevenPlusOne), בלי שורת מבצע נוספת
                break;
        }
    });
}
