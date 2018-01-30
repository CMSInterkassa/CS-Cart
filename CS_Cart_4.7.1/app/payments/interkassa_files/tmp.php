<html>
	<head>
		<meta charset="utf-8" />
		<title>ik pay</title>
		<link rel="stylesheet" href="<?php echo $ik_dir;?>ik.css">
		<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="<?php echo $ik_dir;?>ik.js"></script>
	</head>
	<body>
		<form id="checkout_confirmation" method="post" action="https://sci.interkassa.com/"><?php
		foreach($post_data as $k=>$v) echo "<input type='hidden' name='$k' value='$v'>";
		?></form>
		<div class="ik_block">
			<img src="<?php echo $ik_dir;?>ik_logo.png" width="50%">
			<br>
			<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target=".ik_modal">Выбрать платежную систему</button>
			<div class="modal fade ik_modal" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content" id="plans">
						<div class="modal-body">
							<h1>1. Выберите удобный способ оплаты<br>2. Укажите валюту<br>3. Нажмите 'Оплатить'</h1>
							<div class="row"><?php
foreach(
	getIkPaymentSystems(
		$processor_data['processor_params']['merchant_id'],
		$processor_data['processor_params']['api_id'],
		$processor_data['processor_params']['api_key']
	) as $ps=>$info):
							?><div class="col-sm-3 text-center payment_system">
									<div class="panel panel-warning panel-pricing">
										<div class="panel-heading">
											<img src="<?php echo $ik_dir . 'paysystems/' . $ps;?>.png" alt="<?=$info['title']?>">
										</div>
										<div class="form-group">
									<div class="input-group">
										<div id="radioBtn" class="btn-group radioBtn">
											<?php foreach ($info['currency'] as $currency => $currencyAlias) { ?>
												<?php if ($currency == $shop_cur) { ?>
													<a class="btn btn-primary btn-sm active" data-toggle="fun"
													data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
													<?php } else { ?>
														<a class="btn btn-primary btn-sm notActive" data-toggle="fun"
														data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
														<?php } ?>
														<?php } ?>
													</div>
													<input type="hidden" name="fun" id="fun">
												</div>
											</div>
											<div class="panel-footer">
									<a class="btn btn-block btn-success ik-payment-confirmation" data-title="<?=$ps?>" href="#">Pay with
										<br>
										<strong><?=$info['title']?></strong>
									</a>
								</div>
							</div>
						</div>
					<?endforeach?></div>
				</div>
	    </div>
	  </div>
	</div>
</div>
</body>
</html>
