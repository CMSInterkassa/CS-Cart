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