<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0" />
	<title>Form Validation | Melon - Flat &amp; Responsive Admin Template</title>

	<!--=== CSS ===-->

	<!-- Bootstrap -->
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

	<!-- jQuery UI -->
	<!--<link href="plugins/jquery-ui/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />-->
	<!--[if lt IE 9]>
		<link rel="stylesheet" type="text/css" href="plugins/jquery-ui/jquery.ui.1.10.2.ie.css"/>
	<![endif]-->

	<!-- Theme -->
	<link href="assets/css/main.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/plugins.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/responsive.css" rel="stylesheet" type="text/css" />
	<link href="assets/css/icons.css" rel="stylesheet" type="text/css" />

	<link rel="stylesheet" href="assets/css/fontawesome/font-awesome.min.css">
	<!--[if IE 7]>
		<link rel="stylesheet" href="assets/css/fontawesome/font-awesome-ie7.min.css">
	<![endif]-->

	<!--[if IE 8]>
		<link href="assets/css/ie8.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>

	<!--=== JavaScript ===-->

	<script type="text/javascript" src="assets/js/libs/jquery-1.10.2.min.js"></script>
	<script type="text/javascript" src="plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>

	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/libs/lodash.compat.min.js"></script>

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
		<script src="assets/js/libs/html5shiv.js"></script>
	<![endif]-->

	<!-- Smartphone Touch Events -->
	<script type="text/javascript" src="plugins/touchpunch/jquery.ui.touch-punch.min.js"></script>
	<script type="text/javascript" src="plugins/event.swipe/jquery.event.move.js"></script>
	<script type="text/javascript" src="plugins/event.swipe/jquery.event.swipe.js"></script>

	<!-- General -->
	<script type="text/javascript" src="assets/js/libs/breakpoints.js"></script>
	<script type="text/javascript" src="plugins/respond/respond.min.js"></script> <!-- Polyfill for min/max-width CSS3 Media Queries (only for IE8) -->
	<script type="text/javascript" src="plugins/cookie/jquery.cookie.min.js"></script>
	<script type="text/javascript" src="plugins/slimscroll/jquery.slimscroll.min.js"></script>
	<script type="text/javascript" src="plugins/slimscroll/jquery.slimscroll.horizontal.min.js"></script>

	<!-- Page specific plugins -->
	<!-- Charts -->
	<script type="text/javascript" src="plugins/sparkline/jquery.sparkline.min.js"></script>

	<script type="text/javascript" src="plugins/daterangepicker/moment.min.js"></script>
	<script type="text/javascript" src="plugins/daterangepicker/daterangepicker.js"></script>
	<script type="text/javascript" src="plugins/blockui/jquery.blockUI.min.js"></script>

	<!-- Forms -->
	<script type="text/javascript" src="plugins/uniform/jquery.uniform.min.js"></script> <!-- Styled radio and checkboxes -->
	<script type="text/javascript" src="plugins/select2/select2.min.js"></script> <!-- Styled select boxes -->
	<script type="text/javascript" src="plugins/fileinput/fileinput.js"></script>

	<!-- Form Validation -->
	<script type="text/javascript" src="plugins/validation/jquery.validate.min.js"></script>
	<script type="text/javascript" src="plugins/validation/additional-methods.min.js"></script>

	<!-- Noty -->
	<script type="text/javascript" src="plugins/noty/jquery.noty.js"></script>
	<script type="text/javascript" src="plugins/noty/layouts/top.js"></script>
	<script type="text/javascript" src="plugins/noty/themes/default.js"></script>

	<!-- App -->
	<script type="text/javascript" src="assets/js/app.js"></script>
	<script type="text/javascript" src="assets/js/plugins.js"></script>
	<script type="text/javascript" src="assets/js/plugins.form-components.js"></script>

	<script>
	$(document).ready(function(){
		"use strict";

		App.init(); // Init layout and core plugins
		Plugins.init(); // Init all plugins
		FormComponents.init(); // Init all form-specific plugins
	});
	</script>

	<!-- Demo JS -->
	<script type="text/javascript" src="assets/js/custom.js"></script>
	<script type="text/javascript" src="assets/js/demo/form_validation.js"></script>
</head>

<body>

	<!-- Header -->
	<?php include 'include/header.php'; ?> <!-- /.header -->

	<div id="container">
		<?php include 'include/sidebar.php'; ?>
		<!-- /Sidebar -->

		<div id="content">
			<div class="container">
				<!-- Breadcrumbs line -->
				<div class="crumbs">
					<ul id="breadcrumbs" class="breadcrumb">
						<li>
							<i class="icon-home"></i>
							<a href="index.html">Dashboard</a>
						</li>

						<li>
							<i class="icon-user"></i>
							<a href="users.php">ผู้ใช้งาน</a>
						</li>


						<li class="current">
							เพิ่ม/แก้ไข
						</li>
					</ul>

					<ul class="crumb-buttons">
						<li><a href="charts.html" title=""><i class="icon-signal"></i><span>Statistics</span></a></li>
						<li class="dropdown"><a href="#" title="" data-toggle="dropdown"><i class="icon-tasks"></i><span>Users <strong>(+3)</strong></span><i class="icon-angle-down left-padding"></i></a>
							<ul class="dropdown-menu pull-right">
							<li><a href="form_components.html" title=""><i class="icon-plus"></i>Add new User</a></li>
							<li><a href="tables_dynamic.html" title=""><i class="icon-reorder"></i>Overview</a></li>
							</ul>
						</li>
						<li class="range"><a href="#">
							<i class="icon-calendar"></i>
							<span></span>
							<i class="icon-angle-down"></i>
						</a></li>
					</ul>
				</div>
				<!-- /Breadcrumbs line -->

				<!--=== Page Header ===-->
				<div class="page-header">
					<div class="page-title">
						<h3>ส่วนจัดการผู้ใช้งาน - เพิ่ม/แก้ไข</h3>
						<span>Good morning, John!</span>
					</div>

					<!-- Page Stats -->
					
					<!-- /Page Stats -->
				</div>
				<!-- /Page Header -->

				<!--=== Page Content ===-->
				<div class="row">
					<!--=== Validation Example 1 ===-->
					<div class="col-md-12 col-lg-12">
						

						<div class="tabbable tabbable-custom">
								<ul class="nav nav-tabs">
									<li class="active"><a href="#tab_1_1" data-toggle="tab">ข้อมูลทั่วไป</a></li>
									<li class=""><a href="#tab_1_2" data-toggle="tab">แก้ไขข้อมูล</a></li>
									<li class=""><a href="#tab_1_3" data-toggle="tab">Route</a></li>
								</ul>
								<div class="tab-content">
									<div class="tab-pane active" id="tab_1_1">



									<div class="col-md-3">
										<div class="list-group">
											<li class="list-group-item no-padding">
												<img src="assets/img/demo/avatar-large.jpg" alt="">
											</li>
										
										</div>
									</div>

									<div class="col-md-9">
										<div class="row profile-info">
											<div class="col-md-7">
												<div class="alert alert-info">You will receive all future updates for free!</div>
												<h1>John Doe</h1>

												<dl class="dl-horizontal">
													<dt>Description lists</dt>
													<dd>A description list is perfect for defining terms.</dd>
													<dt>Euismod</dt>
													<dd>Vestibulum id ligula porta felis euismod semper eget lacinia odio sem nec elit.</dd>
													<dd>Donec id elit non mi porta gravida at eget metus.</dd>
													<dt>Malesuada porta</dt>
													<dd>Etiam porta sem malesuada magna mollis euismod.</dd>
													<dt>Felis euismod semper eget lacinia</dt>
													<dd>Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</dd>
												</dl>

												<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt laoreet dolore magna aliquam tincidunt erat volutpat laoreet dolore magna aliquam tincidunt erat volutpat.</p>
											</div>
											<div class="col-md-5">
												<div class="widget">
													<div class="widget-header">
														<h4><i class="icon-reorder"></i> </h4>
													</div>
													<div class="widget-content">
													
													</div>
												</div>
											</div>
										</div> <!-- /.row -->

										<div class="row">
											<div class="col-md-12">
												<div class="widget">
													<div class="widget-content">
														<h3>Last 10 activities</h3>
														<table class="table table-hover table-striped">
															<thead>
																<tr>
														
																	<th>ชื่อเส้นทาง</th>
																
																	<th class="hidden-xs">สร้างขึ้นเมื่อ</th>
																	<th></th>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td>One Day Trip ขับรถเที่ยวใกล้กรุงแบบฮิปๆ ที่เมืองอาร์ต ราชบุรี ทริปนี้ไม่ได้มีแค่โอ่ง</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>
																<tr>
																	<td>รีวิว ต้นนาโฮมสเตย์ จันทบุรี พักผ่อนในบรรยากาศกลางน้ำ กินปูไม่อั้น พร้อมฟินกับอาหาร 3 มื้อ</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>

																<tr>
																	<td>พาชาวโซเชี่ยลไปเที่ยวเกียวโต ชมใบไม้แดง ที่ญี่ปุ่น</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>

																<tr>
																	<td>แช๊ะ ชิม ชิล ! ตะลุยกิน 5 คาเฟ่ใหม่สุดชิค ท้องอิ่ม แถมฟินเว่อร์ ไปกับ Canon EOS M10</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>
															</tbody>
														</table>
													</div>
												</div>
											</div>
											<!-- /Striped Table -->
										</div> <!-- /.row -->
									</div> <!-- /.col-md-9 -->
								


										
									</div>
									<div class="tab-pane" id="tab_1_2">
										<form class="form-horizontal row-border" id="validate-1" action="#">
											<div class="form-group">
												<label class="col-md-3 control-label">ชื่อ-นามสกุล <span class="required">*</span></label>
												<div class="col-md-6">
													<input type="text" name="req1" class="form-control required">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">Email <span class="required">*</span></label>
												<div class="col-md-6">
													<input type="text" name="email1" class="form-control required email">
												</div>
											</div>
											
											<div class="form-group">
												<label class="col-md-3 control-label">รหัสผ่าน <span class="required">*</span></label>
												<div class="col-md-6">
													<input type="password" name="pass1" class="form-control required" minlength="5">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">ยืนยันรหัสผ่าน <span class="required">*</span></label>
												<div class="col-md-6">
													<input type="password" name="cpass1" class="form-control required" minlength="5" equalTo="[name='pass1']">
												</div>
											</div>
											<div class="form-group">
												<label class="col-md-3 control-label">เบอร์ติดต่อ <span class="required">*</span></label>
												<div class="col-md-6">
													<input type="text" name="digits1" class="form-control required digits">
												</div>
											</div>

											


											<div class="form-actions">
												<input type="submit" value="บันทึก" class="btn btn-primary pull-right">
											</div>
										</form>
									</div>
									<div class="tab-pane" id="tab_1_3">
										<table class="table table-hover table-striped">
															<thead>
																<tr>
															
																	<th>ชื่อเส้นทาง</th>
																
																	<th class="hidden-xs">สร้างขึ้นเมื่อ</th>
																	<th></th>
																</tr>
															</thead>
															<tbody>
																<tr>
																	<td>One Day Trip ขับรถเที่ยวใกล้กรุงแบบฮิปๆ ที่เมืองอาร์ต ราชบุรี ทริปนี้ไม่ได้มีแค่โอ่ง</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>
																<tr>
																	<td>รีวิว ต้นนาโฮมสเตย์ จันทบุรี พักผ่อนในบรรยากาศกลางน้ำ กินปูไม่อั้น พร้อมฟินกับอาหาร 3 มื้อ</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>

																<tr>
																	<td>พาชาวโซเชี่ยลไปเที่ยวเกียวโต ชมใบไม้แดง ที่ญี่ปุ่น</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>

																<tr>
																	<td>แช๊ะ ชิม ชิล ! ตะลุยกิน 5 คาเฟ่ใหม่สุดชิค ท้องอิ่ม แถมฟินเว่อร์ ไปกับ Canon EOS M10</td>
													
																	<td class="hidden-xs">12 / 06 / 2559</td>
																	<td></td>
																</tr>
															</tbody>
														</table>
									</div>
								</div>
							</div>



						<!-- /Validation Example 1 -->
					</div>

					<!--=== Validation Example 2 ===-->
					
					<!-- /Validation Example 2 -->
				</div>

				
				<!-- /Page Content -->
			</div>
			<!-- /.container -->

		</div>
	</div>

</body>
</html>