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

<div class="control-group">
	<label class="control-label" for="test_mode">Тестовый режим:</label>
	<div class="controls">
		<input type="checkbox" name="payment_data[processor_params][test_mode]" id="test_mode"{if $processor_params.test_mode} checked="checked" {/if}>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="api_mode">API:</label>
	<div class="controls">
		<input type="checkbox" name="payment_data[processor_params][api_mode]" id="api_mode"{if $processor_params.api_mode} checked="checked" {/if}>
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="api_id">API ID:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][api_id]" id="api_id" value="{$processor_params.api_id}" class="input-text" size="100" />
	</div>
</div>

<div class="control-group">
	<label class="control-label" for="api_key">API KEY:</label>
	<div class="controls">
		<input type="text" name="payment_data[processor_params][api_key]" id="api_key" value="{$processor_params.api_key}" class="input-text" size="100" />
	</div>
</div>
