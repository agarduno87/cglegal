const intro=document.getElementById('intro');
document.getElementById('enter').addEventListener('click',()=>{intro.classList.add('gone');document.body.classList.remove('locked')});
const io=new IntersectionObserver((es)=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}}),{threshold:.14});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
// modales de trayectoria (uno por fundador)
document.querySelectorAll('.cv-btn[data-cv]').forEach(b=>b.addEventListener('click',()=>{const d=document.getElementById('cv-'+b.dataset.cv);if(d)d.showModal()}));
document.querySelectorAll('dialog.cv').forEach(d=>{
  d.querySelector('.cv-x').addEventListener('click',()=>d.close());
  d.addEventListener('click',e=>{if(e.target===d)d.close()});
});
const DICT={en:{
 n1:'Practice',n2:'Team',n3:'Firm',n4:'Insights',n5:'Contact',
 k1:'Law that',k2:'accompanies',k3:'investment.',hp:'Corporate and real-estate firm in Los Cabos and Querétaro. Structure, certainty and strategy for those who invest in Mexico.',cta1:'See practice areas →',
 e1:'What we do — 06',e2:'Who we are',e3:'The firm',e4:'Insights',e5:'Contact',
 'a-h':'Six practice areas. One person accountable.',
 a1:'General corporate',a1p:'Incorporation, corporate governance and contracts.',
 a2:'Real estate & hospitality',a2p:'Acquisition, development and operation, foreign-focused.',
 a3:'Legal audit (due diligence)',a3p:'Full review before buying or investing.',
 a4:'Foreign trade',a4p:'Import, export and compliance.',
 a5:'Government & infrastructure',a5p:'Public procurement, permits and projects.',
 a6:'Philanthropic activities',a6p:'Non-profit structures and authorized donees.',
 'eq-h':'The firm, in its attorneys.',cvbtn:'View background',pd:'To be defined',rolab:'Attorney',cfund:'Founder',ccext:'Foreign Trade',semb:'Photo pending',
 eqnote:'Some team photos and bios pending delivery by the firm. Titles to be confirmed.',
 'mis-h':'Mission',mis:'Comprehensive, excellent legal counsel, close to each client\'s business. (Official text — to confirm.)',
 'vis-h':'Vision',vis:'To be the reference firm for investment in the southern peninsula and the Bajío. (Official text — to confirm.)',
 v1:'Trust',v2:'Honesty',v3:'Commitment',v4:'Excellence',v5:'Teamwork',
 'in-h':'What we see over and over.',c1:'Real estate',c2:'Foreign trade',c3:'Corporate',
 in1:'Buying in a restricted zone: the trust, without myths.',in2:'Customs compliance for the new importer.',in3:'Corporate governance that actually protects shareholders.',
 'ct-h':'Contact',f1:'Name',f2:'Email',f3:'Phone',f4:'Practice area',f5:'Tell us about your matter',f6:'Send',
 al:'Explore this area →',rights:'All rights reserved.',fp1:'Privacy notice',fp2:'Terms & conditions',broc:'Download brochure (PDF)',
 ar4:'Provisional bio: Javier\'s is shown until Alejandro\'s CV is provided.',
 r1:'Javier Culebro Galván founded Culebro Abogados on a simple conviction: that top-tier legal counsel should feel close, not distant.',
 r2:'His work has centered where foreign capital meets the Mexican framework: real-estate and hospitality acquisitions, investment structures, and the legal audits that bring certainty to complex deals.',
 r3:'Beyond the résumé, what sets him apart is a way of practicing: understand the client\'s business first and the legal problem second.',
 r4:'Education, memberships and career details: to be confirmed with the firm.'
}};
function setLang(l){document.documentElement.lang=l;document.querySelectorAll('[data-t]').forEach(el=>{const k=el.dataset.t;if(l==='en'&&DICT.en[k]!=null){if(!el.dataset.es)el.dataset.es=el.innerHTML;el.innerHTML=DICT.en[k]}else if(l==='es'&&el.dataset.es!=null){el.innerHTML=el.dataset.es}});document.querySelectorAll('.lang button').forEach(b=>b.classList.toggle('on',b.dataset.lang===l))}
document.querySelectorAll('.lang button').forEach(b=>b.addEventListener('click',()=>setLang(b.dataset.lang)));

/* --- trampa de tiempo: marca de render --- */
(function(){var ra=document.getElementById('rendered_at'); if(ra) ra.value=Date.now();})();

/* --- si llega con #hash (p.ej. desde un área), salta la intro y baja al form --- */
(function(){var h=location.hash; if(h && h.length>1 && document.querySelector(h)){
  var i=document.getElementById('intro'); if(i){i.classList.add('gone');} document.body.classList.remove('locked');
  setTimeout(function(){var t=document.querySelector(h); if(t) t.scrollIntoView();},60);
}})();

/* --- envío del formulario de contacto --- */
(function(){
  var lf=document.getElementById('lead-form'); if(!lf) return;
  lf.addEventListener('submit', function(e){
    e.preventDefault();
    var st=document.getElementById('form-status');
    var loc=(document.documentElement.lang==='en')?'en':'es';
    var data={}; new FormData(lf).forEach(function(v,k){data[k]=v;}); data.locale=loc;
    st.hidden=false; st.className='form-status'; st.textContent=(loc==='en'?'Sending…':'Enviando…');
    fetch('contact.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
      .then(function(r){ if(!r.ok) throw new Error('http'); return r.json(); })
      .then(function(){
        st.className='form-status ok';
        st.textContent=(loc==='en'?'Thank you. An attorney will contact you shortly.':'¡Gracias! Un abogado te contactará en breve.');
        lf.reset(); var ra=document.getElementById('rendered_at'); if(ra) ra.value=Date.now();
      })
      .catch(function(){
        /* Fallback (p.ej. GitHub Pages sin PHP): abre el correo del visitante */
        var subj=encodeURIComponent('Contacto web — '+(data.name||''));
        var body=encodeURIComponent('Nombre: '+(data.name||'')+'\nCorreo: '+(data.email||'')+'\nTeléfono: '+(data.phone||'')+'\n\n'+(data.message||''));
        st.className='form-status'; st.textContent=(loc==='en'?'Opening your email app…':'Abriendo tu correo…');
        location.href='mailto:contacto@cglegal.com.mx?subject='+subj+'&body='+body;
      });
  });
})();
