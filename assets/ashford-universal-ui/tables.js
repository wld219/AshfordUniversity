
(function(){
"use strict";

  function csvCellValue(cell) {
    const attendance = cell.querySelector('.wa-status-value');
    if (attendance) {
      return attendance.value === 'Present' || attendance.value === 'Absent'
        ? attendance.value : 'Not marked';
    }

    const checked = cell.querySelector(
      'input.wa-status-check:checked'
    );
    if (checked) return checked.dataset.status || 'Not marked';

    const field = cell.querySelector('select, input:not([type="hidden"])');
    if (field) {
      if (field.tagName === 'SELECT') {
        return field.selectedOptions[0]?.textContent.trim() || '';
      }
      if (field.type !== 'checkbox' && field.type !== 'radio') {
        return field.value;
      }
    }

    return cell.textContent.trim();
  }

function boot(){
 document.querySelectorAll('main table, .content table').forEach(function(table,index){
  if(table.dataset.asUiReady==='1')return;
  const body=table.tBodies[0];
  if(!body)return;
  let head=table.tHead, header;
  if(head && head.rows.length)header=head.rows[head.rows.length-1];
  else if(body.rows.length && body.rows[0].querySelector('th'))header=body.rows[0];
  if(!header || header.cells.length < 2)return;
  const rows=Array.from(body.rows).filter(r=>r!==header && r.cells.length>1);
  if(!rows.length)return;
  table.dataset.asUiReady='1';
  const headings=Array.from(header.cells);
  const statusCol=headings.findIndex(h=>/^(status|appointment status)$/i.test(h.textContent.trim()));
  const holder=table.closest('.table-wrap')||table;
  holder.classList.add('as-enhanced-table');
  const tools=document.createElement('div');tools.className='as-ui-toolbar';
  const search=document.createElement('input');search.type='search';search.placeholder='Search displayed rows…';search.setAttribute('aria-label','Search displayed table rows');
  const status=document.createElement('select');status.setAttribute('aria-label','Filter displayed rows by status');
  if(statusCol>=0){
   const opts=['All statuses',...new Set(rows.map(r=>r.cells[statusCol]?.textContent.trim()).filter(Boolean))];
   opts.forEach((val,i)=>{const option=document.createElement('option');option.value=i===0?'':val;option.textContent=val;status.append(option);});
  }
  const clear=document.createElement('button');clear.type='button';clear.textContent='Reset';
  const exportBtn=document.createElement('button');exportBtn.type='button';exportBtn.textContent='Export CSV';
  const count=document.createElement('small');count.className='as-ui-count';count.setAttribute('aria-live','polite');
  tools.append(search);
  if(statusCol>=0)tools.append(status);
  tools.append(clear,exportBtn,count);
  holder.parentNode.insertBefore(tools,holder);
  function update(){
   let shown=0;const term=search.value.trim().toLowerCase();
   rows.forEach(row=>{
    const ok=row.textContent.toLowerCase().includes(term) &&
     (statusCol<0||!status.value||row.cells[statusCol]?.textContent.trim()===status.value);
    row.hidden=!ok;row.style.display=ok?'':'none';if(ok)shown++;
   });
   count.textContent=shown+' / '+rows.length+' displayed-page rows';
  }
  search.addEventListener('input',update);status.addEventListener('change',update);
  clear.addEventListener('click',()=>{search.value='';status.value='';update();});
  let last=-1,asc=true;
  headings.forEach((th,i)=>{
   if(/^(action|actions)$/i.test(th.textContent.trim()))return;
   th.classList.add('as-ui-sort');th.tabIndex=0;th.setAttribute('role','button');
   const sort=()=>{
    asc=last===i?!asc:true;last=i;
    const coll=new Intl.Collator(undefined,{numeric:true,sensitivity:'base'});
    rows.sort((a,b)=>coll.compare(a.cells[i]?.textContent.trim()||'',b.cells[i]?.textContent.trim()||'')*(asc?1:-1));
    rows.forEach(r=>body.appendChild(r));update();
   };
   th.addEventListener('click',sort);
   th.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();sort();}});
  });
  exportBtn.addEventListener('click',()=>{
   const quoted=s=>'"'+String(s).replace(/"/g,'""')+'"';
   const fields=[headings.map(h=>h.textContent.trim()),...rows.filter(r=>!r.hidden).map(r=>Array.from(r.cells).map(c=>csvCellValue(c)))];
   const csv='\uFEFF'+fields.map(line=>line.map(quoted).join(',')).join('\r\n');
   const url=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));
   const anchor=document.createElement('a');anchor.href=url;anchor.download='ashford-'+location.pathname.split('/').pop().replace('.php','')+'.csv';
   document.body.appendChild(anchor);anchor.click();anchor.remove();
   setTimeout(()=>URL.revokeObjectURL(url),1000);
  });
  const crnCol=headings.findIndex(h=>/^crn$/i.test(h.textContent.trim()));
  if(crnCol>=0 && navigator.clipboard){
   rows.forEach(row=>{
    const cell=row.cells[crnCol];if(!cell||cell.querySelector('button'))return;
    const value=cell.textContent.trim();
    if(!/^\d+$/.test(value))return;
    const b=document.createElement('button');b.className='as-copy-crn';b.type='button';b.textContent='Copy';b.setAttribute('aria-label','Copy CRN '+value);
    b.addEventListener('click',()=>navigator.clipboard.writeText(value).then(()=>{b.textContent='Copied';setTimeout(()=>b.textContent='Copy',1500);}).catch(()=>{}));
    cell.appendChild(b);
   });
  }
  update();
 });
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',boot);else boot();
})();
