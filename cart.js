// Объявление в глобальной области видимости
window.isProductInCart = function(productId) {
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    const productIdNum = Number(productId);
    return productIdNum in cart;
};

jQuery(document).ready(function($) {
    const breakpoints = window.themeBreakpoints || {
        tablet: 1024,
        mobile: 375
    };
    let itiInstance = null;
    // Получить корзину из localStorage
    function getCart() {
        return JSON.parse(localStorage.getItem('cart')) || {};
    }

    // Сохранить корзину в localStorage
    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    // Запрос деталей товаров по ID на сервер
    function fetchProductDetails(cart, callback) {
        const productIds = Object.keys(cart); // получаем ID товаров из корзины
        if (productIds.length === 0) {
            callback([]); // если корзина пуста, передаем пустой массив
            return;
        }

        // Делаем AJAX-запрос на сервер для получения данных о товарах
        $.ajax({
            url: cart_ajax_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_cart_product_details',
                ids: JSON.stringify(productIds), // <-- сериализуем массив
                nonce: cart_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Сохраняем данные о товарах в localStorage
                    localStorage.setItem('productDetails', JSON.stringify(response.data));
                    callback(response.data); // передаем данные о товарах в callback
                    /*alert(JSON.stringify(response.data));*/
                } else {
                    alert('Ошибка при получении данных товаров');
                    callback([]); // если ошибка, передаем пустой массив
                }
            }
        });
    }

    // Обновить UI корзины
    function renderCart(products) {
        const cart = getCart(); // получаем корзину из localStorage
        const $list = $('.cart-items-list');
        const $count = $('.cart-count');
        const $total = $('.cart-total-amount');
        const $totalSale = $('.cart-total-sale'); // Элемент для отображения суммы со скидкой
        const $description = $('.cart-description-wrapper');
        const $empty_cart = $('.empty-cart-message');
        const $cart_modal_content = $('.cart-modal-content');
        let count = 0;
        let total = 0;
        let totalSale = 0; // Переменная для суммы со скидкой
        let hasDiscount = false; // Флаг, указывающий, есть ли товары со скидкой
        $list.empty(); // очищаем список товаров в корзине
        $description.show();
        $empty_cart.hide();
        $cart_modal_content.show();

        if (products.length === 0 || Object.keys(cart).length === 0) {
            $empty_cart.show();
            $count.text('');
            $total.text('0 ₽');
            $totalSale.text('0 ₽');
            $totalSale.hide(); // Скрываем поле суммы со скидкой, если корзина пуста
            $description.hide();
            $cart_modal_content.hide();
            return;
        }

        // Для каждого товара добавляем информацию в UI
        products.forEach(product => {
            const qty = cart[product.id] ? cart[product.id].qty : 0;
            const subtotal = product.price * qty;
            count += qty;
            total += subtotal;

            // Проверяем, если есть старая цена
            const oldPrice = product.old_price ? parseFloat(product.old_price) : null;
            const price = parseFloat(product.price);

            // Если есть старая цена, добавляем её к totalSale и устанавливаем флаг
            if (oldPrice) {
                totalSale += oldPrice * qty;
                hasDiscount = true; // Устанавливаем флаг, если есть скидка
            } else {
                totalSale += price * qty;
            }

            // Проверяем, если есть старая цена, то показываем её
            const oldPriceHTML = oldPrice ? `<div class="cart-item-price-old">${product.old_price} ₽</div>` : '';

            $list.append(`
            <li class="cart-item" data-product-id="${product.id}">
                <img src="${product.image}" alt="${product.title}" class="cart-thumb">
                <div class="cart-info">
                    <div class="cart-info-name">
                        <div class="cart-item-name">${product.title}, ${product.volume}</div>
                        <div class="cart-item-brand">${product.brand}</div>
                    </div>
                    <div class="cart-item-price">
                        <div class="cart-item-price-now">${product.price} ₽</div>
                        ${oldPriceHTML}  <!-- вставляем старую цену, если она есть -->
                    </div>
                </div>
                <div class="qty-controls">
                    <button class="decrease-qty">-</button>
                    <span class="item-qty">${qty}</span>
                    <button class="increase-qty">+</button>
                </div>
                <button class="remove-item"></button>
            </li>
        `);
        });

        

        // Обновляем количество товаров и итоговую сумму
        $count.text(`(${count})`);
        $total.text(`${total} ₽`); // Отображаем итоговую сумму без скидок
        $totalSale.text(`${totalSale} ₽`); // Отображаем сумму с учётом скидок

        // Если нет скидок, скрываем блок с суммой со скидкой
        if (!hasDiscount) {
            $totalSale.hide();
        } else {
            $totalSale.show(); // Показываем блок, если есть скидки
        }
    }


/*

    // Добавление товара в корзину
    $('.add-to-cart-button').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('product-id'); // Получаем ID товара
        const cart = getCart(); // Получаем текущую корзину из localStorage

        // Проверяем, есть ли уже товар в корзине
        if (cart[productId]) {
            cart[productId].qty++; // Если товар есть, увеличиваем количество
        } else {
            // Если товара нет в корзине, добавляем его с количеством 1
            cart[productId] = {
                qty: 1
            };
        }
        // Сохраняем обновленную корзину в localStorage
        saveCart(cart);
        document.dispatchEvent(new CustomEvent('productAdded', {
            detail: { productId: productId }
        }));
        updateCartIcon(); // обновляем иконку!
        // Опционально — показать уведомление или обновить UI
        //alert('Товар добавлен в корзину!');
    });
*/
window.bindAddToCartButtons = function() {
    // Удаляем все предыдущие обработчики клика с этих кнопок
    $('.add-to-cart-button').off('click');
    
    // Привязываем новые обработчики для всех кнопок "Добавить в корзину"
    $('.add-to-cart-button').on('click', function(e) {
        e.preventDefault();

        const productId = $(this).data('product-id'); // Получаем ID товара
        const cart = getCart(); // Получаем текущую корзину из localStorage

        // Проверяем, есть ли уже товар в корзине
        if (cart[productId]) {
            cart[productId].qty++; // Если товар есть, увеличиваем количество
        } else {
            // Если товара нет в корзине, добавляем его с количеством 1
            cart[productId] = {
                qty: 1
            };
        }

        // Сохраняем обновленную корзину в localStorage
        saveCart(cart);
        document.dispatchEvent(new CustomEvent('productAdded', {
            detail: { productId: productId }
        }));
        updateCartIcon(); // обновляем иконку!
    });
};

bindAddToCartButtons();




    // Открытие модалки
    $('.cart-button-open').on('click', function() {
        $('.cart-modal-overlay, .cart-modal').fadeIn(); // показываем модалку
    
        const cart = getCart(); // получаем корзину из localStorage
        const $empty_cart = $('.empty-cart-message');
        const $cart_modal_content = $('.cart-modal-content');
        $empty_cart.hide();
        $cart_modal_content.hide();
    
        // Загружаем данные о товарах из сервера
        fetchProductDetails(cart, renderCart); // передаем полученные данные в renderCart для отображения
        initPhoneInputField();
        /*
        const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    
        //  Ограничиваем высоту только на мобильных
        if (vw <= 375) {
            const modalHeight = document.querySelector('.cart-modal').offsetHeight;
            document.body.style.height = `${modalHeight}px`;
            document.body.style.overflow = 'hidden';
            document.documentElement.style.height = `${modalHeight}px`;
            document.documentElement.style.overflow = 'hidden';
        }*/
    });

    // Закрытие модалки
    $(document).on('click', '.cart-modal-close, .cart-modal-overlay', function() {
        $('.cart-modal-overlay, .cart-modal').fadeOut();
    /*
        // Сброс высоты и скролла после закрытия
        document.body.style.height = '';
        document.body.style.overflow = '';
        document.documentElement.style.height = '';
        document.documentElement.style.overflow = '';*/
    });




    // Изменение количества (+)
    $(document).on('click', '.increase-qty', function() {
        const id = $(this).closest('.cart-item').data('product-id');
        const cart = getCart();
        cart[id].qty++; // увеличиваем количество товара
        saveCart(cart); // сохраняем корзину
        updateCartIcon();
        renderCart(getProductDetailsFromLocalStorage()); // перерисовываем корзину без запроса на сервер
    });

    // Изменение количества (–)
    $(document).on('click', '.decrease-qty', function() {
        const $cartItem = $(this).closest('.cart-item'); // Находим родительский элемент товара
        const id = $cartItem.data('product-id');
        let cart = getCart();
        let productDetails = JSON.parse(localStorage.getItem('productDetails')) || []; // Получаем данные о товарах из localStorage

        if (cart[id].qty > 1) {
            cart[id].qty--; // уменьшаем количество товара
            saveCart(cart); // сохраняем обновленную корзину в localStorage
            renderCart(productDetails); // перерисовываем корзину с актуальными данными
        } else {
            // Если количество товара = 1, то удаляем товар из корзины и productDetails
            delete cart[id]; // удаляем товар из корзины
            productDetails = productDetails.filter(product => product.id !== id); // Убираем товар из массива productDetails
            saveCart(cart); // сохраняем обновленную корзину в localStorage
            localStorage.setItem('productDetails', JSON.stringify(productDetails)); // сохраняем обновленные данные товаров
            $cartItem.remove(); // Удаляем сам элемент товара из DOM
            updateCartIcon(); // обновляем иконку!
            document.dispatchEvent(new CustomEvent('productRemoved', {
                detail: { productId: id }
            }));
            renderCart(productDetails); // перерисовываем корзину с актуальными данными
            
        }
    });



    // Удаление товара
    $(document).on('click', '.remove-item', function() {
        const $cartItem = $(this).closest('.cart-item'); // Находим родительский элемент товара
        const id = $cartItem.data('product-id'); // Получаем ID товара
        let cart = getCart(); // Получаем корзину из localStorage
        let productDetails = JSON.parse(localStorage.getItem('productDetails')) || []; // Получаем данные о товарах из localStorage

        // Удаляем товар из корзины
        if (cart[id]) {
            delete cart[id]; // Удаляем товар из корзины
            saveCart(cart); // Сохраняем обновленную корзину в localStorage
        }

        // Удаляем товар из productDetails
        productDetails = productDetails.filter(product => product.id !== id); // Убираем товар из массива данных
        localStorage.setItem('productDetails', JSON.stringify(productDetails)); // Сохраняем обновленный список товаров

        // Удаляем товар из DOM
        $cartItem.remove();
        updateCartIcon(); // обновляем иконку!
        // Отправляем событие о том, что товар удалён
        document.dispatchEvent(new CustomEvent('productRemoved', {
            detail: { productId: id }
        }));

        // Обновляем UI корзины
        renderCart(productDetails); // Перерисовываем корзину с актуальными данными
    });


    // Получить детали товаров из localStorage
    function getProductDetailsFromLocalStorage() {
        return JSON.parse(localStorage.getItem('productDetails')) || [];
    }


    function initPhoneInputField() {
        const $wrappers = $('.phone-input-wrapper');
        
        $wrappers.each(function() {
            const $wrapper = $(this);
            const $input = $wrapper.find('input[type="tel"]');
            const inputId = $input.attr('id'); // Получаем ID инпута
            
            // Определяем суффикс класса по ID
            let classSuffix = '';
            if(inputId === 'phone_personalities') {
                classSuffix = '_personalities';
            }
    
        // Проверяем инициализацию для конкретного инпута
        if ($input.length && !$input.hasClass('iti-initialized' + classSuffix)) {
            const input = $input[0];
        
            // Создаем отдельный экземпляр для каждого поля
            const itiInstance = window.intlTelInput(input, {
                initialCountry: 'ru',
                separateDialCode: true,
                autoHideDialCode: false,
                formatOnDisplay: true,
                searchCountryFlag: false,
                onlyCountries: [
                    'ru', 'kz', 'by', 'md', 'az', 'kg', 'tj', 'uz', 'tm', 'am', 'ge', // СНГ
                    'pl', 'cz', 'bg', 'sk', 'ro', 'se', 'fi', 'ee', 'lt', 'lv', 'ee', // Восточная Европа
                    'cn', 'kr', 'jp', 'mn', // Азия (к ближайшим странам)
                    'no', 'fi', 'dk' // Скандинавия
                ] // Страны СНГ и ближайшие
            });
    
            $input.addClass('iti-initialized' + classSuffix);
            $input.data('iti', itiInstance)
                 .addClass('iti-initialized');
    
            const updatePadding = () => {
                const codeLength = itiInstance.getSelectedCountryData().dialCode.length;
                const currentWidth = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
                
                let paddingLeft;
                
                // Для десктопов (1920px база)
                if (currentWidth >  breakpoints.tablet) {
                    if (codeLength === 1) {
                        paddingLeft = '6.5vw';    // Ваше значение для 1920
                    } else if (codeLength === 2) {
                        paddingLeft = '7.2vw';     // Ваше значение для 1920
                    } else if (codeLength === 3) {
                        paddingLeft = '7.8vw';     // Ваше значение для 1920
                    } else {
                        paddingLeft = (4.5 + codeLength * 1.5) + 'vw'; // Формула для 1920
                    }
                }
                // Для планшетов (1024px база)
                else if (currentWidth > breakpoints.mobile) {
                    if (codeLength === 1) {
                        paddingLeft = '9.5vw';    // Ваше значение для 1920
                    } else if (codeLength === 2) {
                        paddingLeft = '10.5vw';     // Ваше значение для 1920
                    } else if (codeLength === 3) {
                        paddingLeft = '11.5vw';     // Ваше значение для 1920
                    } else {
                        paddingLeft = (4.5 + codeLength * 1.5) + 'vw'; // Формула для 1920
                    }
                }
                // Для мобильных (375px база)
                else {
                    if (codeLength === 1) {
                        paddingLeft = '25.5vw';    // Ваше значение для 1920
                    } else if (codeLength === 2) {
                        paddingLeft = '28.5vw';     // Ваше значение для 1920
                    } else if (codeLength === 3) {
                        paddingLeft = '30vw';     // Ваше значение для 1920
                    } else {
                        paddingLeft = (4.5 + codeLength * 1.5) + 'vw'; // Формула для 1920
                    }
                }
            
                $input.css('padding-left', paddingLeft);
            };
    
            updatePadding();
            updatePhoneInputWrapperScale();
    
            // Обновление отступов при смене страны
            $input.on('countrychange', function () {
                updatePadding();
            });
    
            // Обработчик ввода
            $input.on('input', function () {
                // Убираем все символы, кроме цифр
                this.value = this.value.replace(/[^\d\s\-()]/g, '');
    
                const selectedCountryData = itiInstance.getSelectedCountryData();
                const maxDigits = selectedCountryData && selectedCountryData.possibleLengths
                    ? selectedCountryData.possibleLengths[0] // Получаем максимальную длину номера для страны
                    : 10; // Значение по умолчанию (если данные не доступны)
    
                const currentDigits = this.value.replace(/\D/g, '');
    
                if (currentDigits.length > maxDigits) {
                    const extra = currentDigits.length - maxDigits;
                    const trimmedValue = this.value.slice(0, this.value.length - extra);
                    this.value = trimmedValue;
                }
    
                const phoneNumber = itiInstance.getNumber();
                if (phoneNumber) {
                    localStorage.setItem('userPhoneNumber', phoneNumber);
                }
            });
        }
    });
    }
    

    function updatePhoneInputWrapperScale() {
        const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        
        // Определяем базовое разрешение по брейкпоинтам
        let base;
        if (vw > breakpoints.tablet) {
            base = 1920; // Для больших экранов
        } else if (vw > breakpoints.mobile) {
            base = 1024; // Для планшетов
        } else {
            base = 375; // Для мобильных
        }
        
        const scale = vw / base;
    
        $('.iti__selected-country').each(function () {
            this.style.setProperty('zoom', scale, 'important');
        });
    }

// Запускаем при загрузке и при ресайзе
$(window).on('resize', function () {
    updatePhoneInputWrapperScale();
});

$('.submit-order').on('click', function(e) {
    
    e.preventDefault();
    
    const cart = getCart();

    if (Object.keys(cart).length === 0) {
        alert('Корзина пуста!');
        return;
    }

    const name = $('#name').val().trim();
    const $phoneInput = $('#phone'); // Или другой селектор
    const itiInstance = $phoneInput.data('iti'); // Достаем экземпляр
    
    let phone = '';
    if (itiInstance) {
        phone = itiInstance.getNumber();
    }
    const contactMethod = $('input[name="contact-method"]:checked').val();
/*
    if (!name || !phone) {
        alert(
            'Пожалуйста, заполните имя и телефон.\n' +
            'Имя: ' + name + '\n' +
            'Телефон: ' + phone + '\n' +
            'Связь: ' + (contactMethod ?? 'Не выбрано')
        );
        return;
    }
    else {
        $('.cart-modal-overlay, .cart-modal').fadeOut();
    }*/
    /*alert('ЛОЛ ' + JSON.stringify(cart));*/
    const $empty_name = $('.empty-name-message');
    const $empty_tel = $('.empty-tel-message');
    // Для поля имени
    if (!name) {
        $empty_name.css('display', 'flex'); // Показываем как flex-контейнер
    } else {
        $empty_name.hide(); // Скрываем
    }

    // Для поля телефона
    if (!phone) {
        $empty_tel.css('display', 'flex'); // Показываем как flex-контейнер
    } else {
        $empty_tel.hide(); // Скрываем
    }

    if (name && phone) {
        $('.cart-modal-overlay, .cart-modal').fadeOut();
    }
    else{
        return;
    }


    $.ajax({
        url: cart_ajax_object.ajaxurl,
        type: 'POST',
        dataType: 'json', // ожидаем JSON-ответ
        data: {
            action: 'submit_cart_order',
            nonce: cart_ajax_object.nonce,
            cart: JSON.stringify(cart), // <-- сериализация корзины
            name: name,
            phone: phone,
            contact_method: contactMethod,
        },
        success: function(response) {
            if (response.success) {
                //alert('Заказ успешно оформлен!');
                $('.cart-modal-overlay, .cart-modal').fadeOut();
                // Если надо очистить корзину после успешного заказа:
                localStorage.removeItem('cart');
                updateCartIcon(); // обновляем иконку!
            } else {
                alert('Ошибка: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Ответ сервера:', xhr.responseText);
            alert('Ошибка при отправке заказа. Откройте консоль для деталей.');
        }
    });
    
});


function updateCartIcon() {
    const cart = JSON.parse(localStorage.getItem('cart')) || {};
    const cartImg = document.getElementById('cartIconImage');
    if (!cartImg) return;

    const cartButton = cartImg.closest('.cart-container');
    const cartText = cartButton.querySelector('.cart-text'); // Находим текстовый элемент "Корзина"
    let counterElement = cartButton.querySelector('.cart-counter-all');

    // Подсчет общего количества товаров
    const totalItems = Object.values(cart).reduce((sum, item) => sum + item.qty, 0);
    const hasItems = totalItems > 0;

    // Обновляем иконку
    cartImg.src = hasItems ? cart_ajax_object.icons.full : cart_ajax_object.icons.empty;

    // Работа со счетчиком
    if (hasItems) {
        if (!counterElement) {
            counterElement = document.createElement('span');
            counterElement.className = 'cart-counter-all';
            // Вставляем счетчик В КОНЕЦ контейнера (после всех элементов)
            cartButton.appendChild(counterElement);
        }
        counterElement.textContent = `[${totalItems}]`;
    } else if (counterElement) {
        counterElement.remove();
    }
}

updateCartIcon();
/*
function isProductInCart(productId) {
    const cart = JSON.parse(localStorage.getItem('cart')) || {}; // Получаем корзину из localStorage
    return productId in cart; // Проверяем, есть ли товар в корзине по ID
}

*/

    // Открытие модалки
    //  $('.personalities-button-open').on('click', function() {
  //        $('.cart-modal-overlay, .cart-modal_personalities').fadeIn(); // показываем модалку
  //        initPhoneInputField();
    
 //    });
// 1. Товар тату
// Функция для проверки, относится ли модалка к тату
function isTattooRelated(taxonomy) {
    return taxonomy && taxonomy[0] === 'tattoo';
}

// Универсальная функция открытия модалки
function openPersonalitiesModal(title, taxonomy, requestType, productName = '') {
    // Удаляем класс на всякий случай
    $('.cart-modal_personalities').removeClass('tattoo-theme');
    
    // Устанавливаем заголовок и данные
    $('.personalities-title').html(title);
    $('.submit-order_personalities')
        .data('name', productName)
        .data('taxonomy', taxonomy)
        .data('request-type', requestType);
    
    // Добавляем класс для тату, если это связано с тату
    if (isTattooRelated(taxonomy)) {
        $('.cart-modal_personalities').addClass('tattoo-theme');
    }
    
    // Показываем модалку
    $('.cart-modal_personalities')
        .css('display', 'block')
        .removeClass('modal-slide-out')
        .addClass('modal-slide-in');
    
    $('.cart-modal-overlay').fadeIn();
    initPhoneInputField();
}

// Обработчики для всех кнопок
window.bindTattooBookingButtons = function() {
    // Удаляем все предыдущие обработчики клика с этих кнопок
    $(document).off('click', '.tattoo-product-btn');
    
    // Привязываем новые обработчики для всех кнопок "Забронировать тату"
    $(document).on('click', '.tattoo-product-btn', function(e) {
        e.preventDefault();
        
        const productId = $(this).attr('name') || '';
        const consultationName = $(this).data('name') || '';
        
        // Вызываем модальное окно бронирования
        openPersonalitiesModal('забронируй товар', ['tattoo', 'tattoo'], 'order', consultationName);
        
        document.dispatchEvent(new CustomEvent('tattooBookingClicked', {
            detail: { productId: productId }
        }));
    });
};

// Инициализация при загрузке
bindTattooBookingButtons();
$('.consultation-tattoo-btn').on('click', function() {
    const consultationName = $(this).data('name') || 'базовая';
    openPersonalitiesModal('получи бесплатную<br>консультацию уже сейчас', ['tattoo', 'tattoo'], 'join', consultationName);
});

$('.join-team-btn-tattoo').on('click', function() {
    openPersonalitiesModal('присоединяйся к команде', ['tattoo', 'tattoo'], 'join');
});

$('.certificate-tattoo-btn').on('click', function() {
    openPersonalitiesModal('получи сертификат<br>уже сейчас', ['tattoo', 'tattoo'], 'certificate');
});

// Остальные обработчики (не тату)
$('.join-team-btn-barbershop').on('click', function() {
    openPersonalitiesModal('присоединяйся к команде', ['barbershop', 'barbershop'], 'join');
});

$('.join-team-btn-academy').on('click', function() {
    openPersonalitiesModal('Стань моделью и получи<br>бесплатную стрижку!', ['academy', 'academy'], 'join');
});

$('.buy-course-btn').on('click', function() {
    const courseName = $(this).data('name') || '';
    openPersonalitiesModal('начни обучение<br>уже сейчас', ['academy', 'academy'], 'order', courseName);
});

$('.order-cleaning-btn').on('click', function() {
    openPersonalitiesModal('верни своей обуви<br>идеальный вид', ['cleaning', 'cleaning'], 'order');
});

$('.certificate-barbershop-btn').on('click', function() {
    openPersonalitiesModal('получи сертификат<br>уже сейчас', ['barbershop', 'barbershop'], 'certificate');
});

$('.certificate-cleaning-btn').on('click', function() {
    openPersonalitiesModal('получи сертификат<br>уже сейчас', ['cleaning', 'cleaning'], 'certificate');
});

// Закрытие модалки
$(document).on('click', '.cart-modal-close, .cart-modal-overlay', function() {
    // Запускаем анимацию закрытия
    $('.cart-modal_personalities')
        .removeClass('modal-slide-in')
        .addClass('modal-slide-out');
    
    // После завершения анимации (400ms)
    setTimeout(function() {
        // Удаляем все временные классы и скрываем элементы
        $('.cart-modal_personalities')
            .removeClass('modal-slide-out tattoo-theme')
            .css('display', 'none');
        
        $('.cart-modal-overlay').fadeOut();
    }, 400);
});
    $('.submit-order_personalities').on('click', function(e) {
    
        e.preventDefault();
    
        const name = $('#name_personalities').val().trim();
        const $phoneInput = $('#phone_personalities'); // Или другой селектор
        const itiInstance = $phoneInput.data('iti'); // Достаем экземпляр
    
        let phone = '';
        if (itiInstance) {
            phone = itiInstance.getNumber();
        }
    
        const contactMethod = $('input[name="contact-method_personalities"]:checked').val();

        const $empty_name = $('.empty-name-message');
        const $empty_tel = $('.empty-tel-message');
        // Для поля имени
        if (!name) {
            $empty_name.css('display', 'flex'); // Показываем как flex-контейнер
        } else {
            $empty_name.hide(); // Скрываем
        }
    
        // Для поля телефона
        if (!phone) {
            $empty_tel.css('display', 'flex'); // Показываем как flex-контейнер
        } else {
            $empty_tel.hide(); // Скрываем
        }
    
        if (name && phone) {
            $('.cart-modal-overlay, .cart-modal_personalities').fadeOut();
        }
        else{
            return;
        }

// Получаем дополнительные данные из модального окна
    const modalData = $('.submit-order_personalities').data();
    const taxonomy = modalData.taxonomy || [];
    const requestType = modalData.requestType || '';
    const productName = modalData.name || '';

    // Отправляем на сервер
    $.ajax({
        url: personalities_ajax_object.ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'submit_personalities_order',
            nonce: personalities_ajax_object.nonce,
            name: name,
            phone: phone,
            contact_method: contactMethod,
            taxonomy: taxonomy,       // передаем taxonomy (например, ['tattoo', 'tattoo'])
            request_type: requestType, // 'order', 'join' и т.д.
            product_name: productName  // название курса/товара (если есть)
        },
        success: function(response) {
            if (response.success) {
                //alert('Заказ успешно оформлен!');
                    // Запускаем анимацию закрытия
    $('.cart-modal_personalities')
        .removeClass('modal-slide-in')
        .addClass('modal-slide-out');
    
    // После завершения анимации (400ms)
    setTimeout(function() {
        // Удаляем все временные классы и скрываем элементы
        $('.cart-modal_personalities')
            .removeClass('modal-slide-out tattoo-theme')
            .css('display', 'none');
        
        $('.cart-modal-overlay').fadeOut();
    }, 400);
            } else {
                //alert('Ошибка: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Ответ сервера:', xhr.responseText);
            //alert('Ошибка при отправке заказа. Откройте консоль для деталей.');
        }
    });
});











});