;;; File: emacs-format-file
;;; modified from:  -- http://mccarthy.cslab.pepperdine.edu/~warford/BatchIndentationEmacs.html
;;; Stan Warford
;;; 17 May 2006

(defun emacs-format-function ()
   "Format the whole buffer."
   (setq delete-old-versions 'f) 
   (c-set-style '"bsd")
   (setq indent-tabs-mode nil)
   (setq c-basic-offset 4)
   (indent-region (point-min) (point-max) nil)
   (untabify (point-min) (point-max))
   (save-buffer)
)