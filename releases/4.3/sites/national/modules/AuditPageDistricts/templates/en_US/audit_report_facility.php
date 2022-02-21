<div class="facility_block">

<div id="frame"></div>


  <script>

    document.addEventListener("DOMContentLoaded",
    function(event){

      console.log(event);

    var url_string = window.location.href;
    var url = new URL(url_string);
    var district = url.searchParams.get("district");

    var iframe =document.createElement('iframe');
    iframe.setAttribute('sandbox',"allow-downloads allow-forms allow-scripts allow-popups allow-popups-to-escape-sandbox allow-top-navigation allow-same-origin	");
    iframe.style.width='58rem';
    iframe.style.border='0px';
    iframe.style.height='100rem';
    iframe.setAttribute('src',`https://hris.health.go.ug/hrhdashboard/audit/auditReport?display=ihris&districts=${district}`); 
    
    var frame = document.getElementById('frame');
    console.log(frame);
    frame.appendChild(iframe);

    });
  
</script>
</div>

  <!-- <iframe src="https://hris.health.go.ug/hrhdashboard/audit/auditReport?display=ihris&districts="  sandbox="allow-downloads allow-forms allow-scripts allow-popups allow-popups-to-escape-sandbox allow-top-navigation allow-same-origin	"   style="width:58rem; border:0px; height:100rem;"></iframe> -->


