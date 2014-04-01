{script src="/{baseadmin}/min/?f=plugins/{$pluginName}/js/admin.js" type="javascript"}
<script type="text/javascript">
    $(function(){
        if (typeof MC_convdata == "undefined")
        {
            console.log("MC_convdata is not defined");
        }else{
            MC_convdata.run(baseadmin);
        }
    });
</script>