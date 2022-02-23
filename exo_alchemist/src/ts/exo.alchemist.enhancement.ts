(function ($, Drupal) {

class ExoAlchemistEnhancement {

  public getHash():string {
    return window.location.hash;
  }

  public getHashAsObject():any {
    let hash = this.getHash();
    const object = {};
    if (hash) {
      const array = hash.replace('#', '').split('&');
      for (let i = 0; i < array.length; i++) {
        const element = array[i];
        const parts = element.split('~');
        const elementValues = parts[1].split('|');
        for (let ii = 0; ii < elementValues.length; ii++) {
          const value = elementValues[ii];
          const valueParts = value.split('--');
          if (valueParts.length > 1) {
            object[parts[0]] = {};
            object[parts[0]][valueParts[0]] = valueParts[1];
          }
          else {
            object[parts[0]] = valueParts[0];
          }
        }

      }
    }
    return object;
  }

  public setHash(hash:string) {
    // window.location.hash = hash;
    if(history.pushState) {
      history.pushState({hash: hash}, null, '#' + hash);
    }
    else {
      location.hash = hash;
    }
  }

  public setHashAsObject(object:any):any {
    let hash = '';
    for (const i in object) {
      if (Object.prototype.hasOwnProperty.call(object, i)) {
        const element = object[i];
        if (hash !== '') {
          hash += '&';
        }
        hash += i + '~';
        if (typeof element === 'object') {
          let hashValue = '';
          for (const ii in element) {
            if (Object.prototype.hasOwnProperty.call(element, ii)) {
              const value = element[ii];
              if (hashValue !== '') {
                hashValue += '|';
              }
              hashValue += ii + '--' + value;
            }
          }
          hash += hashValue;
        }
        else {
          hash += element;
        }
      }
    }
    this.setHash(hash);
    return this;
  }

  public getHashForKey(key:string) {
    const parts = this.getHashAsObject();
    return typeof parts[key] !== 'undefined' ? parts[key] : null;
  }

  public setHashForKey(key:string, value:string, id?:string) {
    const object = this.getHashAsObject();
    if (id) {
      if (typeof object[key] === 'undefined') {
        object[key] = {};
      }
      object[key][id] = value;
    }
    else {
      object[key] = value;
    }
    return this.setHashAsObject(object);
  }

  public removeHashForKey(key:string, value:string, id?:string) {
    const object = this.getHashAsObject();
    if (typeof object[key] !== 'undefined') {
      if (id) {
        delete object[key][id];
        let empty = true;
        for (const i in object[key]) {
          if (Object.prototype.hasOwnProperty.call(object, i)) {
            empty = false;
          }
        }
        if (empty === true) {
          delete object[key];
        }
      }
      else {
        delete object[key];
      }
    }
    return this.setHashAsObject(object);
  }

}

Drupal.ExoAlchemistEnhancement = new ExoAlchemistEnhancement();

})(jQuery, Drupal);
