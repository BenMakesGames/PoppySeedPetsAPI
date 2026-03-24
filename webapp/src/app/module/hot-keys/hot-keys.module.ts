import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HotKeyDirective } from './directives/hot-key.directive';

@NgModule({
  declarations: [
    HotKeyDirective
  ],
  exports: [
    HotKeyDirective
  ],
  imports: [
    CommonModule
  ]
})
export class HotKeysModule { }
