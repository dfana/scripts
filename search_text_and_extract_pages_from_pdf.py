"""
Find the string inside a PDF and Extract the pages that match
"""

import re 
from PyPDF2 import PdfFileReader, PdfFileWriter #Debe desacargar installar esta libreria si tiene pip puede instalar pip install pypdf2

__version__ = '1.0.0-beta'
__description__ = '1.0.0'
__autor__ = 'Dante Fana Badia'

def find_pages(str, file_source):
    pages = [] 
    pdf_source = PdfFileReader(file_source)
    for i in range(pdf_source.getNumPages()):
        content = pdf_source.getPage(i).extractText().lower()
        if re.search( str , content) is not None:
            if i not in pages:
                pages.append(i)
    return pages

def extract_pages(pages, file_source, file_result):
    pdf_source = PdfFileReader(file_source)
    pdf_result = PdfFileWriter()
    for i in pages:
        pdf_result.addPage(pdf_source.getPage(i))
    pdf_result.write(file_result)
    file_result.close()    
    
def main():
    str_to_find = r'07-007'#Este es el string que buscara en el pdf 
    pdf_name = r'boletin.pdf'#Esta es la ruta del pdf en el cual buscara
    file_source = open(pdf_name,'rb')#Abre el archivo pdf para poder leerlo 
    file_result = open("{0}.{1}".format(str_to_find,'pdf'), 'wb')#Crea un archivo para poder crear el resultado de la busqueda
    pages = find_pages(str_to_find,file_source)#Con esta funcion obtiene la pagina que alla concidido con la busqueda
    extract_pages(pages,file_source,file_result)#Con esta funcion extrae las paginas y la escribe en el documento que creamos arriba

if __name__ == '__main__':
    main()