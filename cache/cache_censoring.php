<?php

define('PUN_CENSOR_LOADED', 1);

$search_for = array (
  0 => '%(?<=[^\\p{L}\\p{N}])(gay)(?=[^\\p{L}\\p{N}])%iu',
  1 => '%(?<=[^\\p{L}\\p{N}])(fag[\\p{L}\\p{N}]*?)(?=[^\\p{L}\\p{N}])%iu',
  2 => '%(?<=[^\\p{L}\\p{N}])(weeaboo[\\p{L}\\p{N}]*?)(?=[^\\p{L}\\p{N}])%iu',
  3 => '%(?<=[^\\p{L}\\p{N}])(dark world)(?=[^\\p{L}\\p{N}])%iu',
  4 => '%(?<=[^\\p{L}\\p{N}])(lightsworn[\\p{L}\\p{N}]*?)(?=[^\\p{L}\\p{N}])%iu',
  5 => '%(?<=[^\\p{L}\\p{N}])(gadgets)(?=[^\\p{L}\\p{N}])%iu',
  6 => '%(?<=[^\\p{L}\\p{N}])(geargia[\\p{L}\\p{N}]*?)(?=[^\\p{L}\\p{N}])%iu',
  7 => '%(?<=[^\\p{L}\\p{N}])(Nice\\!\\!\\!)(?=[^\\p{L}\\p{N}])%iu',
  8 => '%(?<=[^\\p{L}\\p{N}])(sylvan)(?=[^\\p{L}\\p{N}])%iu',
  9 => '%(?<=[^\\p{L}\\p{N}])(Zac)(?=[^\\p{L}\\p{N}])%iu',
  10 => '%(?<=[^\\p{L}\\p{N}])(I know\\!\\!)(?=[^\\p{L}\\p{N}])%iu',
  11 => '%(?<=[^\\p{L}\\p{N}])(Sion)(?=[^\\p{L}\\p{N}])%iu',
  12 => '%(?<=[^\\p{L}\\p{N}])(Nice\\!\\!)(?=[^\\p{L}\\p{N}])%iu',
  13 => '%(?<=[^\\p{L}\\p{N}])(Nice\\!)(?=[^\\p{L}\\p{N}])%iu',
  14 => '%(?<=[^\\p{L}\\p{N}])(Trinity Force)(?=[^\\p{L}\\p{N}])%iu',
  15 => '%(?<=[^\\p{L}\\p{N}])(triforce)(?=[^\\p{L}\\p{N}])%iu',
  16 => '%(?<=[^\\p{L}\\p{N}])(weeb[\\p{L}\\p{N}]*?)(?=[^\\p{L}\\p{N}])%iu',
  17 => '%(?<=[^\\p{L}\\p{N}])(a asmodeus)(?=[^\\p{L}\\p{N}])%iu',
);

$replace_with = array (
  0 => 'Victoria\'s Secret Commercial',
  1 => 'asmodeus',
  2 => 'true enjoyers of anime',
  3 => 'sperg world',
  4 => 'chemosworn',
  5 => 'autism',
  6 => 'autisms',
  7 => 'Quality Posting!!!',
  8 => 'Pure Maiden',
  9 => 'Hernia',
  10 => 'Let\'s Bounce!!',
  11 => 'Purest Maiden',
  12 => 'More Quality Posting!!',
  13 => 'Greatest Quality of Posting!',
  14 => 'Tons of Damage',
  15 => 'Tonnes of Damage',
  16 => 'fans of sophisticated and philosophical animated cartoons',
  17 => 'an asmodeus',
);

?>