{* $Id: interkassa.tpl  $cas *}

<div class="control-group">
	<label class="control-label" for="merchant_id">Индефикатор кассы:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][merchant_id]" id="merchant_id" value="{$processor_params.merchant_id}" class="input-text" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="sekret_key">Секретный ключ:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][sekret_key]" id="sekret_key" value="{$processor_params.sekret_key}" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="test_key">Тестовый ключ:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][test_key]" id="test_key" value="{$processor_params.test_key}" class="input-text" size="100" />
	</div>
</div>

<div style="margin:10px 0; font-weight bold;">Вставьте следующие значения в соответствующие поля в своем кабинете "Интеркассы":</div>

<div class="control-group">
	<label class="control-label" for="test_key">Url успешной оплаты:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][url_success]" id="url_success" value="http://sitename/?dispatch=payment_notification.success&payment=interkassa" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="test_key">Url неудачной оплаты:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][url_fail]" id="url_fail" value="http://sitename/?dispatch=payment_notification.error&payment=interkassa" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="test_key">Url ожидания проведения платежа:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][url_wait]" id="url_wait" value="http://sitename/?dispatch=payment_notification.return&payment=interkassa" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="test_key">Url взаимодействия:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][url_process]" id="url_process" value="http://sitename/?dispatch=payment_notification.notify&payment=interkassa" class="input-text" size="100" />
	</div>
</div>