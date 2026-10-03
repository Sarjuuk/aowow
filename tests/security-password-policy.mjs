// Shared PHP/browser vectors and the actual signup/reset/change form validators.
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const read = path => readFileSync(new URL('../' + path, import.meta.url), 'utf8');
const vectors = JSON.parse(read('tests/security-password-vectors.json'));
const policy = read('static/js/password-policy.js');
const context = {};
runInNewContext(policy, context);
let checks = 0;
function check(actual, expected, message) { ++checks; assert.equal(actual, expected, message); }
for (const [password, valid] of vectors)
    check(context.g_isNewPasswordValid(password), valid, 'Shared Unicode/byte policy');
check(context.g_isNewPasswordValid('a'.repeat(15) + '\ud800'), false, 'Unpaired surrogate fails');

// Extract the real inline scripts, so indexing the email field as a password is caught.
for (const name of ['signup', 'password']) {
    const template = read('template/bricks/inputbox-form-' + name + '.tpl.php');
    const script = template.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1];
    const error = { innerHTML: '' };
    runInNewContext(script, Object.assign(context, {
        LANG: {}, $WH: { ge: () => error },
        $: () => ({ val: () => 'fixture@example.test', focus() {} }),
        g_isEmailValid: () => true, g_isUsernameValid: () => true
    }));
    for (const [password, valid] of vectors) {
        const control = value => ({ value, focus() {} });
        const pass = control(password), confirm = control(password), email = control('fixture@example.test');
        const elements = name === 'signup' ? [control('user7'), pass, confirm, email] : [email, pass, confirm];
        elements.password = pass; elements.c_password = confirm;
        check(context.inputBoxValidate({ elements }) !== false, valid, 'Real ' + name + ' form boundary');
        if (valid) {
            confirm.value += 'different';
            check(context.inputBoxValidate({ elements }), false, 'Real ' + name + ' form confirmation');
        }
    }
}

// Run the actual account change handler with a minimal jQuery transport fixture.
let submit;
const fields = {};
const contextAccount = {
    g_isNewPasswordValid: context.g_isNewPasswordValid,
    LANG: {}, alert() {}, document: {}, Dialog: Object.assign(function () {}, { templates: {} }), Listview: { templates: {} },
    $WH: { trim: value => value.trim() },
    $: selector => {
        if (typeof selector === 'object') return { ready: callback => callback() };
        if (selector === 'form#change-password') return { submit: callback => { submit = callback; } };
        if (selector.startsWith('input[name=')) return { val: () => fields[selector], 0: { focus() {} } };
        return { submit() {}, change() {}, click() {} };
    }
};
runInNewContext(read('static/js/account.js'), contextAccount);
for (const [password, valid] of vectors) {
    fields['input[name=currentPassword]'] = 'old-password';
    fields['input[name=newPassword]'] = fields['input[name=confirmPassword]'] = password;
    check(submit() !== false, valid, 'Real account password-change boundary');
}
fields['input[name=currentPassword]'] = 'same password long';
fields['input[name=newPassword]'] = fields['input[name=confirmPassword]'] = ' same password long ';
check(submit(), true, 'Spaces are significant when comparing old/new passwords');

console.log(`PASS: ${checks} password policy/form JavaScript checks`);
