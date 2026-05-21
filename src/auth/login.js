document.getElementById("login-form").addEventListener("submit",async e=>{
    e.preventDefault();

    const res=await fetch("../api/login.php",{
        method:"POST",
        body:JSON.stringify({
            email:email.value,
            password:password.value
        })
    });

    const data=await res.json();

    if(data.success){
        window.location="manage_users.html";
    }else{
        alert("Login failed");
    }
});
