<div class="facility_block">
  <script>
    var url_string = window.location.href;
    var url = new URL(url_string);
    var district = url.searchParams.get("district");
    
</script>
  <iframe src="https://hris.health.go.ug/hrhdashboard/audit/auditReport?display=ihris&districts=<?php echo $_GET['district'] ?>"  sandbox="allow-downloads allow-forms allow-scripts allow-popups allow-popups-to-escape-sandbox allow-top-navigation allow-same-origin	"   style="width:58rem; border:0px; height:100rem;"></iframe>

</div>


