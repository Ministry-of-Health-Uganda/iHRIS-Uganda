<?xml version="1.0" encoding="UTF-8"?>   
<xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:csd="urn:ihe:iti:csd:2013"
  exclude-result-prefixes="csd xsl"
  >     
  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>     
  <xsl:template match="/">         
    <xsl:for-each select="/csd:CSD/csd:orgranizationDirectory/csd:orgranization/csd:credential">
      <form name='csd_orgranization_credential' parent_form='csd_orgranization'>
	<xsl:attribute name="id"><xsl:value-of select="concat(../@entityID,'/',csd:codedType/@codingScheme,'@@@',csd:codedType/@code)"/></xsl:attribute>
	<xsl:attribute name="parent_id"><xsl:value-of select="../@entityID"/></xsl:attribute>
	<xsl:attribute name="created"><xsl:value-of select="translate(substring(../csd:record/@created,1,19),'T',' ')"/></xsl:attribute>
	<xsl:attribute name="modified"><xsl:value-of select="translate(substring(../csd:record/@updated,1,19),'T',' ')"/></xsl:attribute>
	<field name='csd_orgranization_credential_type' type='MAP'>
	  <value form='csd_orgranization_credential_type'><xsl:value-of select="concat(csd:codedType/@codingScheme,'@@@',csd:codedType/@code)"/></value>
	</field>
	<xsl:if test="csd:number">
	  <field name='number' type='STRING_LINE'><xsl:value-of select="csd:number"/></field>
	</xsl:if>
	<xsl:if test="csd:issuingAuthority">
	  <field name='issuing_authority' type='STRING_LINE'><xsl:value-of select="csd:issuingAuthority"/></field>
	</xsl:if>
	<xsl:if test="csd:credentialIssueDate">
	  <field name='issue_date' type='DATE_YMD'>
	    <year><xsl:value-of select="substring-before(csd:credentialIssueDate,'-')"/></year>
	    <month><xsl:value-of select="substring-before(substring-after(csd:credentialIssueDate,'-'),'-')"/></month>
	    <day><xsl:value-of select="substring-after(substring-after(csd:credentialIssueDate,'-'),'-')"/></day>
	  </field>
	</xsl:if>
	<xsl:if test="csd:credentialRenewalDate">
	  <field name='renewal_date' type='DATE_YMD'>
	    <year><xsl:value-of select="substring-before(csd:credentialRenewalDate,'-')"/></year>
	    <month><xsl:value-of select="substring-before(substring-after(csd:credentialRenewalDate,'-'),'-')"/></month>
	    <day><xsl:value-of select="substring-after(substring-after(csd:credentialRenewalDate,'-'),'-')"/></day>
	  </field>
	</xsl:if>

      </form>
    </xsl:for-each>
  </xsl:template> 
</xsl:stylesheet>
