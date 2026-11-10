
(() => {
  'use strict';

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

  function enhance(table, index) {
    if (table.dataset.ashfordEnhanced === '1') return;
    const body = table.tBodies[0], head = table.tHead;
    if (!body || !head || !body.rows.length || !head.rows.length) return;
    const headings = [...head.rows[head.rows.length - 1].cells];
    if (headings.length < 2) return;
    const rows = [...body.rows];
    if (rows.some(row => row.cells.length !== headings.length)) return;
    table.dataset.ashfordEnhanced = '1';
    const wrap = table.closest('.table-wrap') || table.parentElement;
    if (wrap) wrap.classList.add('ashford-table-wrap');
    const controls = document.createElement('div'); controls.className = 'ashford-tools';
    const search = document.createElement('input'); search.type = 'search';
    search.placeholder = 'Search rows on this page…'; search.setAttribute('aria-label','Search table rows');
    const status = document.createElement('select'); status.setAttribute('aria-label','Filter by status');
    const statusCol = headings.findIndex(h=>/^status$/i.test(h.textContent.trim()));
    if (statusCol >= 0) {
      const values = [...new Set(rows.map(r=>r.cells[statusCol].textContent.trim()).filter(Boolean))].sort();
      for(const value of ['',...values]) {
        const opt=document.createElement('option');opt.value=value;
        opt.textContent=value||'All statuses';status.append(opt);
      }
    }
    const download = document.createElement('button');download.type='button';download.textContent='Export CSV';
    const clear = document.createElement('button');clear.type='button';clear.textContent='Clear';
    const count = document.createElement('span');count.className='ashford-count';count.setAttribute('aria-live','polite');
    controls.append(search);
    if(statusCol>=0)controls.append(status);
    controls.append(download,clear,count);
    const anchor=table.closest('.table-wrap')||table;
    anchor.parentNode.insertBefore(controls,anchor);
    function filter(){
      const term=search.value.trim().toLocaleLowerCase(); let shown=0;
      for(const row of rows){
        const ok=row.textContent.toLocaleLowerCase().includes(term)&&
          (statusCol<0||!status.value||row.cells[statusCol].textContent.trim()===status.value);
        row.hidden=!ok;row.style.display=ok?'':'none';if(ok)shown++;
      }
      count.textContent=`${shown} of ${rows.length} rows on this page`;
    }
    search.addEventListener('input',filter);status.addEventListener('change',filter);
    clear.addEventListener('click',()=>{search.value='';status.value='';filter();search.focus();});
    let last=-1,ascending=true;
    headings.forEach((th,i)=>{
      if(/^(actions?|select)$/i.test(th.textContent.trim()))return;
      th.classList.add('ashford-sort');th.tabIndex=0;th.setAttribute('role','button');
      th.setAttribute('aria-label','Sort by '+th.textContent.trim());
      function sort(){
        ascending=last===i?!ascending:true;last=i;
        const cmp=new Intl.Collator(undefined,{numeric:true,sensitivity:'base'});
        rows.sort((a,b)=>cmp.compare(a.cells[i]?.textContent.trim()||'',b.cells[i]?.textContent.trim()||'')*(ascending?1:-1));
        rows.forEach(r=>body.append(r));filter();
      }
      th.addEventListener('click',sort);
      th.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();sort();}});
    });
    const quoted=v=>'"'+String(v).replace(/"/g,'""')+'"';
    download.addEventListener('click',()=>{
      const records=[headings.map(h=>h.textContent.trim()),...rows.filter(r=>!r.hidden).map(r=>[...r.cells].map(c=>csvCellValue(c)))];
      const csv='\uFEFF'+records.map(record=>record.map(quoted).join(',')).join('\r\n');
      const url=URL.createObjectURL(new Blob([csv],{type:'text/csv;charset=utf-8'}));
      const a=document.createElement('a');a.href=url;
      a.download='ashford-'+location.pathname.split('/').pop().replace(/\.php$/,'')+'-'+(index+1)+'.csv';
      document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
    filter();
  }
  function initialize(){document.querySelectorAll('table').forEach(enhance);}
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initialize);
  else initialize();
})();
