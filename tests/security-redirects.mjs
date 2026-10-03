// Compare actual PHP return targets with browser URL semantics; no HTTP requests.
import fs from 'node:fs';
import assert from 'node:assert/strict';
const vectors=JSON.parse(fs.readFileSync(0,'utf8'));
let checks=0;
for(const {base,input,output} of vectors) {
    const current=new URL('/db/?locale=fr',base);
    const destination=new URL(output,current);
    assert.equal(destination.origin,current.origin,`Return target must stay on browser origin: ${String(input)}`);checks++;
    assert.equal(destination.username,'');assert.equal(destination.password,'');checks+=2;
    if(output!=='.')assert.ok(!output.startsWith('//') && output.startsWith('/'));
}
console.log(`PASS: ${checks} browser return-target checks`);
