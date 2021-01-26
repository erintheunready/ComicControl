<?php //install-footer.php - outputs footer for installation pages 


//include javascript for managing general jquery on the page
?>	
	<script>
		$('.tooltip').hover(function(){
			$(this).find('.tooltip-help').css("display","block");
			$(this).find('.tooltip-help').animate({top:'60px',opacity:1}, 200);
		},function(){
			$(this).find('.tooltip-help').animate({top:'70px',opacity:0}, 200, function(){
				$(this).css("display","none");
			});
		});	
	</script>
</body>
</html>