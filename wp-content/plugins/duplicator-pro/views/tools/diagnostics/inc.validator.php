<?php
	$scan_run = (isset($_POST['action']) && $_POST['action'] == 'duplicator_recursion') ? true :false;
	$ajax_nonce	= wp_create_nonce('DUP_CTRL_Tools_runScanValidator');
?>

<style>
	div#hb-result {padding: 10px 5px 0 5px; line-height:20px; font-size: 12px}
</style>

<!-- ==========================================
THICK-BOX DIALOGS: -->
<?php
	$confirm1 = new DUP_PRO_UI_Dialog();
	$confirm1->title			= DUP_PRO_U::__('Run Validator', 'duplicator');
	$confirm1->message			= DUP_PRO_U::__('This will run the scan validation check.  This may take several minutes.  Do you want to Continue?', 'duplicator');
	$confirm1->progressOn		= false;
	$confirm1->jsCallback		= 'DupPro.Tools.runScanValidator()';
	$confirm1->initConfirm();
?>

<!-- ==============================
SCAN VALIDATOR -->
<div class="dup-box">
	<div class="dup-box-title">
		<i class="fa fa-check-square-o"></i>
		<?php DUP_PRO_U::_e("Scan Validator", 'duplicator'); ?>
		<div class="dup-box-arrow"></div>
	</div>
	<div class="dup-box-panel" style="display: <?php echo $scan_run ? 'block' : 'none';  ?>">
		<?php
			DUP_PRO_U::_e("This utility will help to find unreadable files and sys-links in your environment  that can lead to issues during the scan process.  ");
			DUP_PRO_U::_e("The utility will also shows how many files and directories you have in your system.  This process may take several minutes to run.  ");
			DUP_PRO_U::_e("If there is a recursive loop on your system then the process has a built in check to stop after a large set of files and directories have been scanned.  ");
			DUP_PRO_U::_e("A message will show indicated that that a scan depth has been reached. If you have issues with the package scanner (step 2) during the build process then try to add "
			. "The paths below to your file filters to allow the scanner to finish.");
		?>
		<br/><br/>


		<button id="scan-run-btn" type="button" class="button button-large button-primary" onclick="DupPro.Tools.ConfirmScanValidator()">
			<?php DUP_PRO_U::_e("Run Scan Integrity Validation"); ?>
		</button>

		<script id="hb-template" type="text/x-handlebars-template">
			<b>Scan Path:</b> <?php echo DUPLICATOR_PRO_WPROOTPATH ?> <br/>
			<b>Scan Results</b><br/>
			<table>
				<tr>
					<td><b>Files:</b></td>
					<td>{{payload.fileCount}} </td>
					<td> &nbsp; </td>
					<td><b>Dirs:</b></td>
					<td>{{payload.dirCount}} </td>
				</tr>
			</table>
			<br/>

			<b>Unreadable Dirs/Files:</b> <br/>
			{{#if payload.unreadable}}
				{{#each payload.unreadable}}
					&nbsp; &nbsp; {{@index}} : {{this}}<br/>
				{{/each}}
			{{else}}
				<i>No Unreadable items found</i> <br/>
			{{/if}}
			<br/>

			<b>Symbolic Links:</b> <br/>
			{{#if payload.symLinks}}
				{{#each payload.symLinks}}
					&nbsp; &nbsp; {{@index}} : {{this}}<br/>
				{{/each}}
			{{else}}
				<i>No Sym-links found</i> <br/>
				<small>	<?php DUP_PRO_U::_e("Note: Symlinks are not discoverable on Windows OS with PHP"); ?></small> <br/>
			{{/if}}
			<br/>

			<b>Directory Name Checks:</b> <br/>
			{{#if payload.nameTestDirs}}
				{{#each payload.nameTestDirs}}
					&nbsp; &nbsp; {{@index}} : {{this}}<br/>
				{{/each}}
			{{else}}
				<i>No name check warnings located for directory paths</i> <br/>
			{{/if}}
			<br/>

			<b>File Name Checks:</b> <br/>
			{{#if payload.nameTestFiles}}
				{{#each payload.nameTestFiles}}
					&nbsp; &nbsp; {{@index}} : {{this}}<br/>
				{{/each}}
			{{else}}
				<i>No name check warnings located for directory paths</i> <br/>
			{{/if}}

			<br/>
		</script>
		<div id="hb-result"></div>

	</div>
</div>
<br/>

<script>
jQuery(document).ready(function($)
{
	DupPro.Tools.ConfirmScanValidator = function()
	{
		<?php $confirm1->showConfirm(); ?>
	}


	//Run request to: admin-ajax.php?action=DUP_CTRL_Tools_runScanValidator
	DupPro.Tools.runScanValidator = function()
	{
		tb_remove();
		var data = {action : 'DUP_CTRL_Tools_runScanValidator', nonce: '<?php echo $ajax_nonce; ?>', 'scan-recursive': true};

		$('#hb-result').html('<?php DUP_PRO_U::_e("Scanning Environment... This may take a few minutes."); ?>');
		$('#scan-run-btn').html('<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> Running Please Wait...');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			data: data,
			success: function(data) {DupPro.Tools.IntScanValidator(data)},
			error: function(data) {console.log(data)},
			done: function(data) {console.log(data)}
		});
	}

	//Process Ajax Template
	DupPro.Tools.IntScanValidator= function(data)
	{
		var template = $('#hb-template').html();
		var templateScript = Handlebars.compile(template);
		var html = templateScript(data);
		$('#hb-result').html(html);
		$('#scan-run-btn').html('<?php DUP_PRO_U::_e("Run Scan Integrity Validation"); ?>');
	}
});
</script>

