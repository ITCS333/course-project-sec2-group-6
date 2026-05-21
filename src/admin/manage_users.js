let users=[];

const table=document.getElementById("user-table-body");
const form=document.getElementById("add-user-form");
const search=document.getElementById("search-input");

// تحميل البيانات
async function load(){
    const res=await fetch("../api/index.php");
    const data=await res.json();
    users=data.data;
    render(users);
}

// رسم الجدول
function render(arr){
    table.innerHTML="";
    arr.forEach(u=>{
        let tr=document.createElement("tr");

        tr.innerHTML=`
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.is_admin==1?"Yes":"No"}</td>
        <td>
        <button onclick="del(${u.id})">Delete</button>
        </td>`;

        table.appendChild(tr);
    });
}

// حذف
async function del(id){
    await fetch("../api/index.php?id="+id,{method:"DELETE"});
    load();
}

// إضافة مستخدم
form.addEventListener("submit",async e=>{
    e.preventDefault();

    const body={
        name:document.getElementById("user-name").value,
        email:document.getElementById("user-email").value,
        password:document.getElementById("default-password").value,
        is_admin:document.getElementById("is-admin").value
    };

    await fetch("../api/index.php",{
        method:"POST",
        body:JSON.stringify(body)
    });

    load();
});

// بحث
search.addEventListener("input",()=>{
    let val=search.value.toLowerCase();
    let filtered=users.filter(u=>u.name.toLowerCase().includes(val)||u.email.toLowerCase().includes(val));
    render(filtered);
});

load();
