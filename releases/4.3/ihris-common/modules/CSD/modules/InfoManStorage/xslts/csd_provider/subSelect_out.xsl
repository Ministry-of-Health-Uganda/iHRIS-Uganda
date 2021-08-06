<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <csd:careServicesRequest xmlns:csd="urn:ihe:iti:csd:2013" xmlns="urn:ihe:iti:csd:2013">             
      <function urn="urn:ihe:iti:csd:2014:stored-function:provider-search">
	<requestParams>                     
	  <record>
	    <xsl:if test="/form/@modified">
	      <xsl:attribute name='updated'><xsl:value-of select="translate(substring(/form/@modified,1,19),' ','T')"/></xsl:attribute>
	    </xsl:if>
	  </record>                     
	  <xsl:if test="/form/@start">
	    <start><xsl:value-of select="/form/@start"/></start>
	  </xsl:if>
	  <xsl:if test="/form/@max">
	    <max><xsl:value-of select="/form/@max"/></max>
	  </xsl:if>
	</requestParams>             
      </function>         
    </csd:careServicesRequest>     

  </xsl:template> 
</xsl:stylesheet>
