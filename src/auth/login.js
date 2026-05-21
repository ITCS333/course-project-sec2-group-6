(function(){
    const form=document.getElementById('login-form');
    const emailInp=document.getElementById('email');
    const passInp=document.getElementById('password');
    const msgDiv=document.getElementById('message-container');
    if(form){
        form.addEventListener('submit',function(e){
            if(msgDiv) msgDiv.textContent='';
            const email=emailInp.value.trim();
            const pass=passInp.value;
            if(email===''){e.preventDefault();if(msgDiv) msgDiv.textContent='Email address is required.';return;}
            if(!/^\S+@\S+\.\S+$/.test(email)){e.preventDefault();if(msgDiv) msgDiv.textContent='Please enter a valid email address (e.g., name@domain.com).';return;}
            if(pass===''){e.preventDefault();if(msgDiv) msgDiv.textContent='Password is required.';return;}
        });
    }
})();
