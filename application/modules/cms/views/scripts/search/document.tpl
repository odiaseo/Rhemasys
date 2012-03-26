<div class="doc-preview">  		
	  <table width="100%"  border="0" cellpadding="5" cellspacing="0">
	  	<?php if($this->section[1]){ ?>
		  <tr>
		    <td colspan="2" valign="top"> <?php echo $this->section[1]; ?></td> 	    
		  </tr>
	<?php }
		  if($this->section[2] or $this->section[3]){ ?>	
		  <tr>
		    <td width="50%" valign="top"><?php echo $this->section[2]; ?> </td>
		    <td valign="top"><?php echo ucwords($this->section[3]); ?> </td>
		  </tr>
	<?php }
		  if($this->section[4]){ ?>	
		  <tr>
		    <td colspan="2" valign="top"><?php echo $this->section[4]; ?> </td>
		  </tr>
	<?php }
		  if($this->section[5]){ ?>	
		  <tr>
		    <td colspan="2" valign="top"><?php echo $this->section[5]; ?> </td>
		  </tr>
	<?php }
		  if($this->section[6]){ ?>	
		  <tr>
		    <td colspan="2" valign="top"><?php echo $this->section[6]; ?> </td>
		  </tr>
	<?php }?>
		  	
	</table>
</div>