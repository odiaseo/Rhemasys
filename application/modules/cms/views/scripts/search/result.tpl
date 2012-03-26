 <div id="search-result"> 
	<?php 
	if(count($this->searchResults)){
		$cnt = 0;
		foreach($this->searchResults as $typeId => $result){ ?>			
			<div>
				<h2><?php echo $this->allTypes[$typeId]['title'] . ' (' . count($this->searchResults[$typeId]); ?>)</h2>
					<p><?php echo  $this->allTypes[$typeId]['description']; ?></p>
	<?php			foreach($result as $item){ 
						$cnt++;?>
						<ol start='<?php echo $cnt; ?>'>
							<li><h3>
									<a href="#" id="doc-<?php echo $item['id']; ?>" class="clue-tips" 
										rel="<?php echo $this->itemUrl . $item['id'] . '/type/' . $item['type_id'] ;?>"
										title="<?php echo $item['title']; ?>">
										<?php echo $item['title']; ?>
									</a>
								</h3>
							<p><?php echo  $item['description']; ?></p>							 
							</li>
						</ol>
	<?php			} ?>
			</div>

	<?php	}  
	}else{?> 
		<p>No document found</p>
<?php } ?>	
</div>

