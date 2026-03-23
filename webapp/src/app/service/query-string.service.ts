import { Injectable } from '@angular/core';
import {ParamMap} from "@angular/router";

@Injectable({
  providedIn: 'root'
})
export class QueryStringService {

  constructor() { }

  static parse(p: ParamMap)
  {
    let o: any = {};

    p.keys.forEach(key => {
      const isArray = key.endsWith('[]');
      const value = isArray ? p.getAll(key) : p.get(key);
      const keyPath = (isArray ? key.substr(0, key.length - 2) : key).split('.');

      let oPath = o;

      for(let i = 0; i < keyPath.length; oPath = oPath[keyPath[i]], i++)
      {
        if(i === keyPath.length - 1)
          oPath[keyPath[i]] = value;
        else
          oPath[keyPath[i]] = oPath[keyPath[i]] || {};
      }
    });

    return o;
  }

  static convertToAngularParams(o: any)
  {
    let params: any = {};

    QueryStringService.appendAngularParams(params, o, '');

    return params;
  }

  private static appendAngularParams(params: any, o: any, path: string)
  {
    const keys = Object.keys(o);

    keys.forEach(k => {
      const keyPath = path.length === 0 ? k : path + '.' + k;

      // skip null and empty string
      if(o[k] === null || o[k] === '')
        return;

      // array
      if(Array.isArray(o[k]))
      {
        // skip empty arrays
        if(o[k].length > 0)
          params[keyPath + '[]'] = o[k];

        return;
      }

      // int. string, bool, etc
      if(typeof o[k] !== 'object')
      {
        params[keyPath] = o[k];
        return;
      }

      // objects
      QueryStringService.appendAngularParams(params, o[k], keyPath);
    });
  }

  static parseInt(input: string, d: number): number
  {
    const v = parseInt(input);

    if(isNaN(v) || !isFinite(v) || !Number.isInteger(v))
      return d;

    return v;
  }

  static parseNullableInt(input: string): number|null
  {
    const v = parseInt(input);

    if(isNaN(v) || !isFinite(v) || !Number.isInteger(v))
      return null;

    return v;
  }

  static parseBool(input: any, defaultValue: boolean): boolean
  {
    const s = input.toString().trim().toLowerCase();

    if(s === '')
      return defaultValue;

    if(s === 'false' || s === 'no')
      return false;

    if(s === 'true' || s === 'yes')
      return true;

    const i = parseFloat(s);

    if(isNaN(i))
      return defaultValue;

    return i !== 0;
  }

  static parseArray(input: any): any[]
  {
    return Array.isArray(input) ? input : [ input ];
  }

  static parseNullableBool(input: any): boolean|null
  {
    const s = input.toString().trim().toLowerCase();

    if(s === '' || s === 'null')
      return null;

    if(s === 'false' || s === 'no')
      return false;

    if(s === 'true' || s === 'yes')
      return true;

    const i = parseFloat(s);

    if(isNaN(i))
      return null;

    return i !== 0;
  }
}
