<div class="wrap">
<h2>Keyword Strategy</h2>


<?php if (! function_exists('get_admin_url')): ?>
<p style="color:red;">Your WordPress version is not supported. Please update.</p>
<?php endif; ?>

<p>
	Total keywords: <?php echo $total_keywords; ?>
	<? if ($total_keywords != 0): ?>
			(<a target="_blank" href="<?php echo htmlspecialchars(admin_url('admin-ajax.php').'?action=kws_all_keywords'); ?>">view</a>)
	<? endif; ?>
	<form action="" method="post" enctype="multipart/form-data">
		<input type="hidden" value="upload" name="kws_action" />
		<input type="file" name="file" />
		<input type="submit" value="Upload keywords" class="button" />
		<br />
		File should be in CSV format. Keyword first, then URL.
		<!--<span><input class="button" type="submit" value="Update now" onclick="window.location = window.location.href + '&kws_action=update_now'; this.parentNode.innerHTML = 'Updating... Please wait...'" /></span> -->

	</form>
</p>


<br /><br />

<h3>Options</h3>
<form action="" method="post">
	<input type="hidden" value="save_options" name="kws_action" />
	<table class="form-table">
		<tr valign="top"> 
			<th scope="row"><label for="kws_linker_enabled" style="white-space:nowrap;"> Enable the keyword-URL internal linking</label></th> 
			<td><input name="kws_linker_enabled" type="checkbox" id="kws_linker_enabled" value="1" <?php echo isset($kws_options['linker_enabled']) && !$kws_options['linker_enabled']? '': 'checked="checked"'; ?> /> </td> 
		</tr>
		<!--
		<tr valign="top"> 
			<th scope="row"><label for="kws_tracker_enabled" style="white-space:nowrap;"> Enable the Keyword Strategy javascript tracker</label></th> 
			<td><input name="kws_tracker_enabled" type="checkbox" id="kws_tracker_enabled" value="1" <?php echo isset($kws_options['tracker_enabled']) && !$kws_options['tracker_enabled']? '': 'checked="checked"'; ?> /> </td> 
		</tr>
		-->
		<tr valign="top"> 
			<th scope="row"><label for="kws_header_links" style="white-space:nowrap;"> Allow links in H1-H6 tags</label></th> 
			<td><input name="kws_header_links" type="checkbox" id="kws_header_links" value="1" <?php echo ! $kws_options['header_links']? '': 'checked="checked"'; ?> /> </td> 
		</tr>
		<tr valign="top"> 
			<th scope="row"><label for="kws_wait_days" style="white-space:nowrap;"> New articles shouldn't show links for how many days</label></th> 
			<td><input name="kws_wait_days" type="text" id="kws_wait_days" value="<?php echo $kws_options['wait_days']; ?>" class="small-text" /> default: 0</td> 
		</tr>
		<!--
		<tr valign="top"> 
			<th scope="row"><label for="kws_exact_match" style="white-space:nowrap;"> Minimum monthly searches for imported keywords</label></th> 
			<td><input name="kws_exact_match" type="text" id="kws_exact_match" value="<?php echo $kws_options['exact_match']; ?>" class="small-text" /> default: 140</td> 
		</tr>
		-->
		<tr valign="top"> 
			<th scope="row"><label for="kws_links_article" style="white-space:nowrap;"> Maximum number of links to insert per article</label></th> 
			<td><input name="kws_links_article" type="text" id="kws_links_article" value="<?php echo isset($kws_options['links_article'])? $kws_options['links_article'] : 10; ?>" class="small-text" /> default: 10</td> 
		</tr>
		<tr valign="top"> 
			<th scope="row"><label style="white-space:nowrap;"> Priority of links</label></th> 
			<td>
				<label><input name="kws_links_priority"  type="radio" value="long"  <?php echo $kws_options['links_priority'] == 'traffic'? '' : 'checked="checked"'; ?>	/> Longest Keywords</label>
				<label><input name="kws_links_priority" type="radio" value="traffic" <?php echo $kws_options['links_priority'] == 'traffic'? 'checked="checked"' : ''; ?> /> Highest Traffic Keywords</label>
			</td> 
		</tr>
		<!--
		<tr valign="top"> 
			<th scope="row"><label style="white-space:nowrap;" for="kws_keywords_limit"> Maximum number of keywords to link sitewide</label></th> 
			<td>
				<select name="kws_keywords_limit" id="kws_keywords_limit">
					<?php foreach ($keywords_limits AS $limit): ?>
						<option value="<?php echo $limit; ?>" <?php if($limit == $kws_options['keywords_limit'] || (!isset($kws_options['keywords_limit']) && $limit == 1000)): ?>selected="selected"<?php endif; ?> ><?php echo $limit; ?></option>
					<?php endforeach; ?>
				</select>
				<b>If you are expereincing any performance issues, try to lower this.</b> default: 1000
			</td> 
		</tr>
		-->
		<tr valign="top">
			<th scope="row"><label style="white-space:nowrap;" for="kws_banned_urls"> Disable plugin on these pages:</label></th>
			<td>
				<div style="float:left; margin-right: 5px;">
					<textarea name="kws_banned_urls" rows="4" cols="40" wrap="off" id="kws_banned_urls"><?php echo htmlspecialchars($kws_options['banned_urls']); ?></textarea>
				</div>
				<div>
					Examples:<br />
					<b>/gallery/</b> will match all pages with /gallery/ inside.<br />
					<b>http://www.example.com/341-article</b> will match single article.<br />
					<b>/site/*/content/</b> will match /site/nature/content/, /site/politics/content/, /site/tech/content, etc.
				</div>
			</td>
		</tr>
	</table>
	<p class="submit"> 
		<input type="submit" name="Submit" class="button-primary" value="Save Changes" /> 
	</p> 
</form>

<p>
	Tip: You can put some text inside <b><i>&lt;kwsignore&gt;&lt;/kwsignore&gt;</i></b> tags to avoid plugin inserting links inside.
</p>

</div>
