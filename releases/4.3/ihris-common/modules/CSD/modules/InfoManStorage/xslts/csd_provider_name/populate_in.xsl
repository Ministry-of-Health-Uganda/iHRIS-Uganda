<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider/csd:demographic/csd:name">      
      <form name='csd_provider_name' parent_form='csd_provider'>
	<xsl:attribute name="id"><xsl:value-of select="concat(../../@entityID,'/',@position)"/></xsl:attribute>
	<xsl:attribute name="parent_id"><xsl:value-of select="../../@entityID"/></xsl:attribute>
	<xsl:attribute name="created"><xsl:value-of select="translate(substring(../../csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
	<xsl:attribute name="modified"><xsl:value-of select="translate(substring(../../csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
	<xsl:if test="./csd:honorific">        
	  <field name="honorific" type="STRING_LINE"><xsl:value-of select="./csd:honorific"/></field> 
	</xsl:if>
	<xsl:if test="./csd:surname">        
	  <field name="surname" type="STRING_LINE"><xsl:value-of select="./csd:surname"/></field> 
	</xsl:if>
	<xsl:if test="./csd:forename">        
	  <field name="forename" type="STRING_LINE"><xsl:value-of select="./csd:forename"/></field> 
	</xsl:if>
	<xsl:if test="./csd:suffix">        
	  <field name="suffix" type="STRING_LINE"><xsl:value-of select="./csd:suffix"/></field> 
	</xsl:if>
	<xsl:if test="./csd:otherName">        
	  <field name='other_name' type='ASSOC_LIST'>
	    <xsl:for-each select="./csd:otherName">        
	      <value><xsl:attribute name='key'><xsl:value-of select="position()"/></xsl:attribute><xsl:value-of select="text()"/></value>
	    </xsl:for-each>
	  </field>
	</xsl:if>
	<xsl:if test="./csd:commonName">        
	  <field name='common_name' type='ASSOC_LIST'>
	    <xsl:for-each select="./csd:commonName">        
	      <value><xsl:attribute name='key'><xsl:value-of select="position()"/></xsl:attribute><xsl:value-of select="text()"/></value>
	    </xsl:for-each>
	  </field>
	</xsl:if>
	
      </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
