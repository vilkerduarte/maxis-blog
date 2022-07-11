$.post('',{
    "fingerprint":IDENTITY.fingerprint
},function(res){
    if(res == 'meta requested'){
        $.post('',{"fingerprint":IDENTITY.fingerprint,"metadata":IDENTITY},function(info){
        });
    }
});
