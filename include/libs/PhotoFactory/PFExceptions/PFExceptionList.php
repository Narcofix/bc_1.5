<?php
/**
 * lista delle eccezioni che posso essere lanciate da PhotoFactory
 **/
 
/**
 * Errore nel detect della risorsa
 */
class PFException_PFResourceHelperError extends PFException {}
/**
 * La path della classe richiesta non esiste
 */
class PFException_ClassPathError extends PFException {}
/**
 * Libreria GD non caricata
 */
class PFException_GDNotLoaded extends PFException {}
/**
 * La cartella temporanea non esiste e non e' possibile crearle
 */
class PFException_TempDirNotExist extends PFException {}
/**
 * Non e' possibile ottenere i permessi di scrittura nella cartella temporanea
 */
class PFException_TempDirNotWritable extends PFException {}
/**
* Stringa binaria non e' un file immagine
*/
class PFException_InvalidFileUrl extends PFException {}
/**
 * Stringa binaria non e' un file immagine
 */
class PFException_GetFileError extends PFException {}
/**
 * Stringa binaria non e' un file immagine
 */
class PFException_InvalidImageFile extends PFException {}
/**
 * Stringa binaria non valida
 */
class PFException_InvalidBinaryString extends PFException {}
/**
 * Risorsa GD non valida
 */
class PFException_InvalidGDResource extends PFException {}
/**
 * Dimensioni Risorsa GD non valide
 */
class PFException_InvalidGDDimensions extends PFException {}
/**
 * eccezioni relative alle operazioni
 */
/**
 * eccezione generica operazioni
 */
class PFException_Op extends PFException {}

class PFException_OpArgsError extends PFException {}
/**
 * l'operazione richiede almeno un argomento
 */
class PFException_OpNeedsArguments extends PFException {}
/**
 * operazione non implementata
 */
class PFException_OpNameError extends PFException {}
/**
 * numero argomenti minore di quello richiesto dall'operazione
 */
class PFException_OpTotArguments extends PFException {}
?>