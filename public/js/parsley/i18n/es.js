// ParsleyConfig definition if not already set
// Validation errors messages for Parsley
// Load this after Parsley

Parsley.addMessages('es', {
  defaultMessage:     "Este valor parece ser inválido.",
  type: {
    email:            "Este valor debe ser un correo válido.",
    url:              "Este valor debe ser una URL válida.",
    number:           "Este valor debe ser un número válido.",
    integer:          "Este valor debe ser un número válido.",
    digits:           "Este valor debe ser entero.",
    alphanum:         "Este valor debe ser alfanumérico."
  },
  notblank:           "Este valor no debe estar en blanco.",
  required:           "Dato Obligatorio.",
  pattern:            "Este valor es incorrecto.",
  regexrfc:           "El RFC no es correcto XAX010101X1X",
  regexpureza:        "La pureza solo debe tener un decimal",
  regexfloat:         "Se requiere numero entero con 1,2 o 3 decimales",
  regexemail:         "El email no es correcto.",
  regexsafepassword:  "Error la contraseña debe tener al menos 1 mayúscula, 1 minúscula, 1 dígito, 1 carácter especial:!,#,$,%,& y una longitud de al menos 8",
  solonumeros:        "El valor solo debe tener numeros",
  codigopostal:       "Solo se admiten 5 numeros",
  decimalesconfigurados:       "Debe ser un numero con "+numerodecimales+" decimales",
  
  min:                "Este valor no debe ser menor que %s.",
  max:                "Este valor no debe ser mayor que %s.",
  existencias:        "Error el almacen no cuenta con existencias suficientes, Existencias actuales :%s.",
  utilidad:        "Error la utilidad debe ser mayor a 0",
  range:              "Este valor debe estar entre %s y %s.",
  minlength:          "Este valor es muy corto. La longitud mínima es de %s caracteres.",
  maxlength:          "Este valor es muy largo. La longitud máxima es de %s caracteres.",
  length:             "La longitud de este valor debe estar entre %s y %s caracteres.",
  tecnico:            "Debes asignar un tecnico al servicio.",
  mincheck:           "Debe seleccionar al menos %s opciones.",
  maxcheck:           "Debe seleccionar %s opciones o menos.",
  check:              "Debe seleccionar entre %s y %s opciones.",
  equalto:            "Este valor debe ser idéntico."
});

Parsley.setLocale('es');
