<?php
function mostrarTx($valores, $tipo) {
  $tipos_inicio = [
    'th' => 'px-4 py-1 text-center text-xs font-medium uppercase tracking-wider whitespace-nowrap',
    'td' => 'px-4 py-1 text-center whitespace-nowrap'
  ];
  $tag = $tipo === 0 ? 'th' : 'td';
  $clase_base = $tipos_inicio[$tag];

  foreach ($valores as $valor) {
   if ($valor === null) {
     $valor_str = '';
   }  else {
     $valor_str = (string) $valor;
  }

   $contiene_html = strpos($valor_str, '<div') !== false || strpos($valor_str, '<span') !== false;
   $contiene_div = strpos($valor_str, '<div') !== false;
   $valor_mostrar = $contiene_html ? $valor_str : htmlspecialchars($valor_str);
   $es_largo = $tipo === 1 && strlen(strip_tags($valor_str)) > 20 && !$contiene_div;

   $clase_extra = $es_largo ? ' max-w-[200px] truncate' : '';
   $title_attr = $es_largo && $valor_mostrar ? ' title="' . htmlspecialchars(strip_tags($valor_str)) . '"' : '';

   echo "<$tag class=\"$clase_base$clase_extra\"$title_attr>$valor_mostrar</$tag>";
 }

}


