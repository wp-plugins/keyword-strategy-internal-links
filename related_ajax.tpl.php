<?php if ($urls): ?>
<p>
			<?php $links_needed = ($kws_options['related_links']? $kws_options['related_links'] : 1) - $item['links'] ?>
Insert the keyword '<?php echo htmlspecialchars($keyword); ?>' into <?php echo $links_needed < 0 ? 0 : $links_needed ?> of these suggested pages. We'll autolink them together to create an internal link.
</p>
	<?php foreach ($urls AS  $item): ?>
		<?php extract($item); ?>
	<p>
		<a href="<?php echo htmlspecialchars($url); ?>" target="_blank"><?php echo htmlspecialchars($url); ?></a>,
		<?php echo $url_links; ?> links
		<br />
		<a target="_blank" href="post.php?post=<?php echo $post_id; ?>&amp;action=edit" title="Edit this page">Edit&nbsp;Page</a>
		<a class="thickbox" href="<?php echo admin_url('admin-ajax.php'); ?>?action=kws_related_urls&amp;kws_keyword=<?php echo (urlencode($keyword)); ?>&amp;not_appropriate=<?php echo urlencode($url); ?>" title="Hide this suggestion">Not&nbsp;Appropriate</a>
	</p>
	<?php endforeach; ?>
<?php else: ?>
	<div style="text-align: center; margin-top:20px;">
		<p>Unfortunately, we weren't able to find any other pages on your site to use for links.</p> 		<p>Create more content and we'll try again later.</p>
	</div>
<?php endif; ?>
