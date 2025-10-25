document.addEventListener('DOMContentLoaded', function(){

    const tableBody = document.getElementById('addressBookBody');
    const editModal = document.getElementById('rmRealtorClientEditModal');
    const closeEditBtn = document.getElementById('closeRealtorClientEditModal');

    if(!tableBody) return;

    tableBody.addEventListener('click', function(e){
        const target = e.target;
        const row = target.closest('tr');
        if(!row) return;
        const clientId = row.dataset.clientId;
        if(!clientId) return;

        // DELETE
        if(target.closest('.ab-deleteClient')){
            if(!confirm('Are you sure?')) return;

            const btn = target.closest('.ab-deleteClient');
            const originalText = btn.textContent;
            btn.textContent = 'Deleting...';
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action','delete_realtor_client_ajax');
            formData.append('nonce',abClientData.delete_nonce);
            formData.append('client_id',clientId);

            fetch(abClientData.ajax_url,{method:'POST',body:formData})
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    row.remove();
                } else {
                    alert('Error: '+data.data);
                    btn.textContent=originalText; btn.disabled=false;
                }
            }).catch(()=>{alert('Network error'); btn.textContent=originalText; btn.disabled=false;});
        }

        // EDIT
        if(target.closest('.ab-editClientDetails')){
            editModal.style.display='flex';

            fetch(abClientData.ajax_url,{
                method:'POST',
                body:new URLSearchParams({
                    action:'fetch_realtor_client_ajax',
                    nonce:abClientData.edit_nonce,
                    client_id:clientId
                })
            })
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    const client = data.data;
                    document.getElementById('edit_realtor_client_id').value=client.client_id;
                    document.getElementById('edit_realtor_client_full_name').value=client.full_name;
                    document.getElementById('edit_realtor_client_email').value=client.email;
                    document.getElementById('edit_realtor_client_phone').value=client.phone;
                    document.getElementById('edit_realtor_client_notes').value=client.note;
                    document.getElementById('edit_realtor_client_status').value=client.status;
                    document.getElementById('editRealtorClientPreviewAvatar').src=client.profile_picture || abClientData.default_avatar;
                } else { alert('Failed to fetch data'); editModal.style.display='none'; }
            }).catch(()=>{alert('Network error'); editModal.style.display='none';});
        }

    });

    // CLOSE EDIT MODAL
    if(closeEditBtn){
        closeEditBtn.addEventListener('click',()=>editModal.style.display='none');
        editModal.addEventListener('click', e=>{if(e.target===editModal) editModal.style.display='none';});
    }

    // IMAGE PREVIEW
    const editInput = document.getElementById('edit_realtor_client_profile_picture');
    if(editInput){
        editInput.addEventListener('change', function(){
            const file=this.files[0];
            if(file){
                const reader=new FileReader();
                reader.onload=e=>document.getElementById('editRealtorClientPreviewAvatar').src=e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    // UPDATE CLIENT
    const form = document.getElementById('editRealtorClientForm');
    if(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('action','update_realtor_client_ajax');
            formData.append('nonce',abClientData.edit_nonce);

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent='Updating...'; submitBtn.disabled=true;

            fetch(abClientData.ajax_url,{method:'POST',body:formData})
            .then(res=>res.json())
            .then(data=>{
                if(data.success){
                    alert('Client updated!');
                    form.reset();
                    editModal.style.display='none';
                    setTimeout(()=>window.location.reload(),500);
                } else { alert('Error: '+data.data); }
            }).catch(()=>alert('Network error'))
            .finally(()=>{submitBtn.textContent=originalText; submitBtn.disabled=false;});
        });
    }

});
