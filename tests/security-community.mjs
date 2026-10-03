// Execute the actual reply merge/loader source with a small synthetic UI and transport fixture.
import fs from 'node:fs';
import vm from 'node:vm';
import assert from 'node:assert/strict';
let checks=0;
const check=(ok,label)=>{checks++;assert.ok(ok,label);};
const context={console,Number,document:{},$:()=>({ready(){}})};vm.createContext(context);
vm.runInContext(fs.readFileSync(new URL('../setup/tools/filegen/templates/global.js/comments.js',import.meta.url),'utf8'),context);
const reply=(id,body='body')=>({id,body,creationdate:'2026/01/01 00:00:00',totalReplies:250});
const comment={id:1,nreplies:250,replies:[1,2,3,4,5].map(id=>reply(id))};
context.comment=comment;
context.MergeCommentReplies(comment,[reply(250),reply(3,'edited')]);
check(comment.replies.map(r=>r.id).join(',')==='1,2,3,4,5,250','focus and edits retain stable chronological ordering');
check(comment.replies[2].body==='edited' && comment.nreplies===250,'merge replaces edits and preserves full server count');
// Focused fetches initialize the contiguous frontier before merging distant replies.
comment.replyOffset=5;
let click,request,updates=0,hidden=false;
const link={click(fn){click=fn;},hide(){hidden=true;},show(){hidden=false;}};
const cell={append(){},find(){return {remove(){}};}};
context.$={ajax(options){request=options;}};
context.CreateAjaxLoader=()=>({});context.MessageBox=()=>{};
context.Listview={templates:{comment:{updateReplies(){updates++;}}}};
context.SetupShowMoreComments({find(selector){return selector==='.show-more-replies'?link:cell;}},comment);
click();check(request.data.offset===5 && hidden,'more starts at contiguous frontier despite a focused tail');
request.success(Array.from({length:100},(_,i)=>reply(i+6)));
check(comment.replyOffset===105 && comment.replies.length===106 && updates===1,'successful bounded batch advances offset while deduplicating');
click();check(request.data.offset===105,'subsequent request continues from acknowledged offset');
request.error();check(!hidden && comment.replyOffset===105,'failed request restores link without losing offset');
click();request.success(Array.from({length:100},(_,i)=>reply(i+106)));
click();request.success(Array.from({length:45},(_,i)=>reply(i+206)));
check(comment.replyOffset===250 && comment.replies.length===250,'incremental loading can reach every reply including focused duplicates');
check(comment.replies.every((r,i)=>r.id===i+1),'no reply skipped during focus plus sequential loading');
context.MergeCommentReplies(comment,[]);check(comment.nreplies===250,'empty batch preserves total');
const listview=fs.readFileSync(new URL('../setup/tools/filegen/templates/global.js/listview_templates.js',import.meta.url),'utf8');
// Run the actual missing-reply branch, including its success handler and retry guard.
const branch=listview.slice(listview.indexOf('highlightReply: function('),listview.indexOf('/* Ok, we have found the reply to highlight. */'));
vm.runInContext('var highlight = ({'+branch+'} }).highlightReply;',context);
const focused={id:1,replies:[reply(1),reply(2)],nreplies:250};
let retried;
context.Listview.templates.comment.highlightReply=(...args)=>{retried=args;};
context.highlight(focused,250,false);
check(request.data.focus===250 && !('offset' in request.data),'legacy reply highlight asks for containing page');
request.success([reply(250)]);
check(focused.replyOffset===2 && focused.replies.length===3 && retried[1]===250 && retried[2]===true,'focused page does not advance sequential frontier and retries once');
request=null;context.highlight(focused,999,true);check(request===null,'unknown reply cannot cause a fetch loop');
if(process.argv.includes('--pages')) {
    const scripts=JSON.parse(fs.readFileSync(0,'utf8'));
    for(const script of scripts)for(const [hash,search,ids,expected] of [
        ['#comments:id=201','?item=1',[], '?go-to-comment&id=201'],
        ['#comments:id=201:reply=499','?item=1',[], '?go-to-comment&id=499'],
        ['#comments:id=201','?item=1',[201], null],
        ['#comments:id=201','?item=1&coPage=3',[],null],
        ['#comments','?item=1',[],null]
    ]) {
        let redirected=null;
        vm.runInNewContext(script,{location:{hash,search,replace(url){redirected=url;}},LANG:{tab_comments:'Comments'},Listview:function(){},lv_comments:ids.map(id=>({id}))});
        check(redirected===expected,'actual rendered anchor resolves missing comment/reply without looping');
    }
}
console.log(`PASS: ${checks} incremental reply UI checks`);
