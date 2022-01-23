window.addEvent('domready', function(){
    
  new Sortables('#dashboard_reports,#dash_report',{
    clone: true,
    revert: true,
    opacity: 0.7
  });
  
});