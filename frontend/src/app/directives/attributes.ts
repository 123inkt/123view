import {Directive, ElementRef, input, OnChanges, Renderer2, SimpleChanges} from '@angular/core';

/**
 * Adds [attrs] directive to an elements and converts an object of attributes into HTML attributes on the element.
 */
@Directive({selector: '[attrs]', standalone: true})
export class Attributes implements OnChanges {
  public attrs = input.required<{ [key: string]: boolean | number | string }>();

  constructor(private readonly el: ElementRef, private readonly renderer: Renderer2) {
  }

  public ngOnChanges(changes: SimpleChanges): void {
    if (changes['attrs'] && this.attrs) {
      for (const [key, value] of Object.entries(this.attrs())) {
        if (value === true) {
          this.renderer.setAttribute(this.el.nativeElement, key, '');
        } else if (value !== false) {
          this.renderer.setAttribute(this.el.nativeElement, key, String(value));
        }
      }
    }
  }
}
