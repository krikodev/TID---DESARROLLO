class Tippy {
  init(label, obj) {
    this.label = document.querySelectorAll(label);
    this.obj = obj;
    tippy(this.label, {
      content: this.obj['content'],
      // followCursor: 'horizontal',
      animation: this.obj['animation'],
      placement: this.obj['placement'],
      allowHTML: true,
    });
  }

}

export { Tippy };