(function(){
  const QKEY='pp_queue';
  const pdata = (window.__UNIT_DATA__||{}).project||{};
  function getQ(){try{return JSON.parse(localStorage.getItem(QKEY)||'[]')}catch(e){return[]}}
  function setQ(q){localStorage.setItem(QKEY, JSON.stringify(q))}
  async function flush(){
    if(!pdata.webhook_url) return;
    let q=getQ(), next=[];
    for(const item of q){
      try{
        const r=await fetch(pdata.webhook_url,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(item)});
        if(!r.ok) throw new Error('HTTP '+r.status);
      }catch(e){ item.retries=(item.retries||0)+1; item.last_error=e.message; next.push(item); }
    }
    setQ(next);
    if(next.length) setTimeout(flush, Math.min(30000, 5000*(next[0].retries||1)));
  }
  window.addEventListener('online', flush);
  window.addEventListener('load', flush);

  document.addEventListener('click', e=>{
    if(!e.target.classList.contains('btn-mark')) return;
    const scoreEl = e.target.parentElement.querySelector('.score');
    // Placeholder marking for now:
    const res = {score:1, max_score:1};
    scoreEl.textContent = `Score: ${res.score}/${res.max_score}`;
    const udata = window.__UNIT_DATA__;
    const item = {
      project_id: udata.project?.id, section_id: udata.section?.id, unit_id: udata.unit?.id,
      exercise_id: 0, payload: {}, score: res.score, max_score: res.max_score, ts: Date.now()
    };
    const q=getQ(); q.push(item); setQ(q); flush();
  });
})();
