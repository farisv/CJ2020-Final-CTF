function parseQuery(query) {
    let ret = {};

    if (!query) {
        return ret;
    }

    query.split('&').forEach(function(value) {
        const param = value.split('=');
        let key = decodeURIComponent(param[0]);
        let keys = key.split('][');
        let keys_last = keys.length - 1;

        if (/\[/.test(keys[0]) && /\]$/.test(keys[keys_last])) {
            keys[keys_last] = keys[keys_last].replace(/\]$/, '');
            keys = keys.shift().split('[').concat(keys);
            keys_last = keys.length - 1;
        } else {
            keys_last = 0;
        }

        if (param.length === 2) {
            const val = decodeURIComponent(param[1]);
            if (keys_last) {
                let cur = ret;
                for (let i = 0; i <= keys_last; i++) {
                    key = keys[i] === '' ? cur.length : keys[i];
                    if (key && !/^[a-zA-Z]+$/g.test(key)) {
                        console.warn(`invalid key: ${key}`);
                        continue;
                    }

                    let curVal;
                    if (i < keys_last) {
                        curVal = cur[key] || (keys[i+1] && isNaN(keys[i+1]) ? {} : []);
                    } else {
                        curVal = val;
                    }
                    cur = cur[key] = curVal;
                }
            } else {
                if (Object.prototype.toString.call(ret[key]) === '[object Array]') {
                    ret[key].push(val);
                } else if ({}.hasOwnProperty.call(ret, key) ) {
                    ret[key] = [ret[key], val];
                } else {
                    ret[key] = val;
                }
            }
        } else if (key) {
            ret[key] = undefined;
        }
    });

    return ret;
}
