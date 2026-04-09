<?php
function render_personalities_modal_html() {
    ?>
	<div class="cart-modal-overlay"></div>
	<div class="cart-modal_personalities">
		<div class="personalities-modal-render" id="personalities-modal-render">
			<div class="personalities-modal-header">
				<div class="personalities-title-wrapper"> 
                   <div class="personalities-title">присoединяйся к команде</div>
				   <button class="cart-modal-close"></button>
                </div>
                <div class="personalities-description-label">
                    <span>Заполните форму и укажите где с вами лучше связаться.</span> 
                    <span>Наш менеджер ответит вам в течение часа </span></div>
			</div>
				<div class="personalities-wrapper">
					<div class="name-tel-form-group">
						<div class="form-group"><input type="text" id="name_personalities" placeholder="Ваше имя" class="form-control "> </div>
						<p class="empty-name-message">укажите имя</p>
					</div>	
					<div class="name-tel-form-group">	
						<div class="form-group phone-input-wrapper"><input type="tel" id="phone_personalities" class="form-control"> </div>
						<p class="empty-tel-message">укажите номер телефона</p>
                    </div>
				</div>
				<div class="personalities-final-wrapper">
					<div class="contact-methods_personalities">
						<label class="switch">
							<input type="radio" name="contact-method_personalities" value="telegram" checked> <span class="custom-radio"></span> <span class="label-text">Telegram</span> </label>
						<label class="switch">
							<input type="radio" name="contact-method_personalities" value="whatsapp"> <span class="custom-radio"></span> <span class="label-text">WhatsApp</span> </label>
						<label class="switch">
							<input type="radio" name="contact-method_personalities" value="phone"> <span class="custom-radio"></span><span class="label-text">По телефону</span> </label>
					</div>
                    <div class="cart-button-wrapper">
						<button class="cart-checkout submit-order_personalities">оформить заявку</button>
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
	<?php
}
add_action('wp_footer', 'render_personalities_modal_html');
