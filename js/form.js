const scriptURL = 'https://script.google.com/macros/s/AKfycbxUt0Yh-2__8Vj3iJixYoDlplUwpKgAOOn9S7xH4nNC9qfBS7v2AH-lj6Bn0otWLTaF-A/exec'
const form = document.forms['submit-to-google-sheet']
const msg = document.getElementById("msg")

form.addEventListener('submit', e => {
  e.preventDefault()
  fetch(scriptURL, { method: 'POST', body: new FormData(form)})
    .then(response =>{
      setTimeout(function(){
        alert("Message Sent Successfully!")
      }, 2000)
      form.reset()
    })
    .catch(error => console.error('Error!', error.message))
})