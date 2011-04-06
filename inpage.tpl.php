<div class="wrap">
<h2 style="float:left;">Keyword Strategy</h2>

<h2 class="nav-tab-wrapper">
<a class="nav-tab " href="<?= KWS_PLUGIN_URL ?>">Overview</a>
	<a class="nav-tab nav-tab-active" href="<?= KWS_PLUGIN_URL . '&kws_action=inpage' ?>">Insert Keywords</a>
	<a class="nav-tab" href="<?= KWS_PLUGIN_URL . '&kws_action=related' ?>">Links Needed</a>
</h2>

<p>
	These are keyword variations that you could enter into your pages. Click "Edit Page" to open the article and find a good place to put the text in. <br /> "Blacklist" will blacklist the keyword in your Keyword Strategy project. Click "Detach from URL" if you don't want to associate this keyword with this page.
</p>
<p>Last update: <?= $kws_options['last_update']? date('Y-m-d H:i', $kws_options['last_update']).", {$inpage_total_keywords} keywords" : 'Never'?> <span><input class="button" type="submit" value="Update now" onclick="window.location = '<?= KWS_PLUGIN_URL ?>' + '&kws_action=update_now_inpage'; this.parentNode.innerHTML = 'Updating... Please wait...'" /></span></p>
<p> Your keywords will update automatically every day.


<? if ($inpage): ?>
<form action="<?= KWS_PLUGIN_URL ?>" method="post">
<div class="tablenav">
<div class="alignleft actions">
	<input type="hidden" name="kws_action" value="inpage_form" />
	<select name="inpage_action" style="margin: 10px 0; width:130px;">
		<option value="none">Bulk Actions</option>
		<option value="blacklist">Blacklist</option>
		<option value="detach">Detach</option>
	</select>
	<input type="submit" value="Apply" class="button-secondary action" />
</div>
<div class="alignright actions">
	<?= kws_pagination('top', $page_args) ?>
</div>
</div>
<table class="wp-list-table widefat fixed pages" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
			<th scope="col" class="manage-column" style=""><span>Keyword</span></th><th scope="col" class="manage-column" style=""><span>URL</span></th>
		</tr>
	</thead>
	
	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
		<th scope="col" class="manage-column"><span>Keyword</span></th>
		<th scope="col" class="manage-column"><span>URL</span></th>	</tr>
	</tfoot>

	<tbody>
	<? foreach ($inpage AS $item): ?>
			<tr class="alternate author-self status-publish format-default iedit" valign="top">
			<th scope="row" class="check-column"><input type="checkbox" name="keyword[]" value="<?= $item['id'] ?>" /></th>
				<td class="post-title page-title column-title"><strong><?= htmlspecialchars($item['keyword']) ?></strong>


<div class="row-actions">
<span class="edit"><a target="_blank" href="post.php?post=<?= $item['post_id'] ?>&amp;action=edit" title="Edit this page">Edit Page</a> |</span>
	<span class="trash"><a class="submitdelete" title="Blacklist keyword" href="<?= KWS_PLUGIN_URL ?>&kws_action=inpage_form&inpage_action=blacklist&keyword[]=<?= $item['id'] ?>">Blacklist Keyword</a> |
	</span>
	<span class="trash"><a href="<?= KWS_PLUGIN_URL ?>&kws_action=inpage_form&inpage_action=detach&keyword[]=<?= $item['id'] ?>" title="Detach keyword from this URL" rel="permalink">Detach from this URL</a></span>
</div>
</td>			
<td class=""><a target="_blank" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['url']) ?></a></td>
						
					</tr>
	<? endforeach; ?>
		</tbody>
</table>
<div class="tablenav">
	<div class="alignleft actions">
		<select name="inpage_action2" style="margin-top: 10px; width:130px;">
			<option value="none">Bulk Actions</option>
			<option value="blacklist">Blacklist</option>
			<option value="detach">Detach</option>
		</select>
		<input type="submit" value="Apply" name="apply2" class="button-secondary action" />
	</div>
	<div class="alignright actions">
		<?= kws_pagination('bottom', $page_args) ?>
	</div>
</div>

</form>
</div>
<? else: ?>

<p>
	--- No keywords available ---
</p>

<? endif; ?>
</div>
