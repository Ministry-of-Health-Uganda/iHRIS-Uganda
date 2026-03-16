<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:providerDirectory/csd:provider">
    <form name='csd_provider'>
      <xsl:attribute name="id"><xsl:value-of select="@entityID"/></xsl:attribute>
      <xsl:attribute name="created"><xsl:value-of select="translate(substring(csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
      <xsl:attribute name="modified"><xsl:value-of select="translate(substring(csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
      <field name="entityID" type="STRING_LINE"><xsl:value-of select="@entityID"/></field> <!-- ID and ENTITYID match -->
      <field name="source_directory" type="STRING_LINE"><xsl:value-of select="csd:record/@sourceDirectory"/></field>
      <field name="csd_provider_status" type="MAP"><value form='csd_provider_status'><xsl:value-of select="./csd:record/@status"/></value></field>
      <xsl:if test="./csd:demographic/csd:gender">        
        <field name="csd_provider_gender" type="MAP"><value form='csd_provider_gender'><xsl:value-of select="./csd:demographic/csd:gender"/></value></field>
      </xsl:if>
      <field name="csd_provider_type" type="MAP_MULT">
	<xsl:for-each select="csd:codedType">
	  <value form='csd_provider_type'><xsl:value-of select="concat(@codingScheme,'@@@',@code)"/></value>
	</xsl:for-each>
      </field>
      <xsl:if test="csd:specialty">
      <field name="csd_provider_specialty" type="MAP_MULT">
        <xsl:for-each select="csd:specialty">
	  <value form='csd_provider_specialty'><xsl:value-of select="concat(@codingScheme,'@@@',@code)"/></value>
	</xsl:for-each>
      </field>
      </xsl:if>
      <xsl:if test="csd:language">
      <field name="csd_language" type="MAP_MULT">
	<xsl:for-each select="csd:language">
	  <value form='csd_language'><xsl:value-of select="concat(@codingScheme,'@@@',@code)"/></value>
	</xsl:for-each>       
      </field>
      </xsl:if>
      <xsl:variable name='dob' select='./csd:demographic/csd:dateOfBirth'/>
      <xsl:if test="$dob">
        <field name="date_of_birth" type="DATE_YMD">
	  <year><xsl:value-of select="substring-before($dob,'-')"/></year>
	  <month><xsl:value-of select="substring-before(substring-after($dob,'-'),'-')"/></month>
	  <day><xsl:value-of select="substring-after(substring-after($dob,'-'),'-')"/></day>
	</field>
      </xsl:if>
    </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
