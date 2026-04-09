<?php
function render_cart_modal_html() {
    ?>
	<div class="cart-modal-overlay"></div>
	<div class="cart-modal">
		<div class="cart-modal-render" id="cart-modal-render">
			<div class="cart-modal-header">
				<div class="cart-title-wrapper"> <span class="cart-title">Корзина</span> <span class="cart-count"></span>
					<!-- Вставляется JS -->
				</div>
				<button class="cart-modal-close"></button>
			</div>
            <p class="empty-cart-message">Корзина пуста. Добавьте в корзину хотя бы один товар</p>
			<div class="cart-modal-content">
				<ul class="cart-items-list">
					<!-- Список товаров добавляется через JS -->
				</ul>
				<div class="cart-description-wrapper">
					<div class="cart-total-wrapper">
						<div class="cart-total-label">сумма:</div>
						<div class="cart-total-price">
							<div class="cart-total-sale">0 ₽</div>
							<!-- Вставляется JS -->
							<div class="cart-total-amount">0 ₽</div>
						</div>
					</div>
					<div class="cart-description-label">В данный момент товары доступны только по предзаказу. Заполните форму и укажите где с вами лучше связаться. Наш менеджер ответит вам в течение часа</div>
					<div class="cart-order-wrapper">
						<div class="cart-personalities-wrapper">
						    <div class="name-tel-form-group">
								<div class="form-group">
									<input type="text" id="name" placeholder="Ваше имя" class="form-control "> </div>
								<p class="empty-name-message">укажите имя</p>
							</div>	
							<div class="name-tel-form-group">	
								<div class="form-group phone-input-wrapper">
									<input type="tel" id="phone" class="form-control"> </div>
								<p class="empty-tel-message">укажите номер телефона</p>
							</div>
						</div>
						<div class="cart-final-wrapper">
							<div class="contact-methods">
								<label class="switch">
									<input type="radio" name="contact-method" value="telegram" checked> <span class="custom-radio"></span> <span class="label-text">Telegram</span> </label>
								<label class="switch">
									<input type="radio" name="contact-method" value="whatsapp"> <span class="custom-radio"></span> <span class="label-text">WhatsApp</span> </label>
								<label class="switch">
									<input type="radio" name="contact-method" value="phone"> <span class="custom-radio"></span><span class="label-text">По телефону</span> </label>
							</div>
                            <div class="cart-button-wrapper">
							    <button class="cart-checkout submit-order">оформить заявку</button>
                                <div class="cart-privacy-policy">
                                    <span class="privacy-text">Оставляя заявку вы соглашаетесь c</span>
                                    <?php
                                    $privacy_policy_page_id = get_option('cart_privacy_policy_page');
                                    $privacy_policy_url = $privacy_policy_page_id ? get_permalink($privacy_policy_page_id) : '#'; // Если не выбрана страница, ссылка будет #
                                    ?>
                                    <a href="<?php echo esc_url($privacy_policy_url); ?>" class="privacy-link">
                                        <span class="privacy-link-text">политикой конфиденциальности</span>
                                    </a>
                                </div>
                            </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action('wp_footer', 'render_cart_modal_html');
